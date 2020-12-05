const map = (typeof Map === "function") ? new Map() : (function () {
    const keys = [];
    const values = [];

    return {
        has(key) {
            return keys.indexOf(key) > -1;
        },
        get(key) {
            return values[keys.indexOf(key)];
        },
        set(key, value) {
            if (keys.indexOf(key) === -1) {
                keys.push(key);
                values.push(value);
            }
        },
        delete(key) {
            const index = keys.indexOf(key);
            if (index > -1) {
                keys.splice(index, 1);
                values.splice(index, 1);
            }
        },
    }
})();

let createEvent = (name)=> new Event(name, {bubbles: true});
try {
    new Event('test');
} catch(e) {
    // IE does not support `new Event()`
    createEvent = (name)=> {
        const evt = document.createEvent('Event');
        evt.initEvent(name, true, false);
        return evt;
    };
}

function assign(ta) {
    if (!ta || !ta.nodeName || ta.nodeName !== 'TEXTAREA' || map.has(ta)) return;

    let heightOffset = null;
    let clientWidth = null;
    let cachedHeight = null;

    function init() {
        const style = window.getComputedStyle(ta, null);

        if (style.resize === 'vertical') {
            ta.style.resize = 'none';
        } else if (style.resize === 'both') {
            ta.style.resize = 'horizontal';
        }

        if (style.boxSizing === 'content-box') {
            heightOffset = -(parseFloat(style.paddingTop)+parseFloat(style.paddingBottom));
        } else {
            heightOffset = parseFloat(style.borderTopWidth)+parseFloat(style.borderBottomWidth);
        }
        // Fix when a textarea is not on document body and heightOffset is Not a Number
        if (isNaN(heightOffset)) {
            heightOffset = 0;
        }

        update();
    }

    function changeOverflow(value) {
        {
            // Chrome/Safari-specific fix:
            // When the textarea y-overflow is hidden, Chrome/Safari do not reflow the text to account for the space
            // made available by removing the scrollbar. The following forces the necessary text reflow.
            const width = ta.style.width;
            ta.style.width = '0px';
            // Force reflow:
            /* jshint ignore:start */
            ta.offsetWidth;
            /* jshint ignore:end */
            ta.style.width = width;
        }

        ta.style.overflowY = value;
    }

    function getParentOverflows(el) {
        const arr = [];

        while (el && el.parentNode && el.parentNode instanceof Element) {
            if (el.parentNode.scrollTop) {
                arr.push({
                    node: el.parentNode,
                    scrollTop: el.parentNode.scrollTop,
                })
            }
            el = el.parentNode;
        }

        return arr;
    }

    function resize() {
        if (ta.scrollHeight === 0) {
            // If the scrollHeight is 0, then the element probably has display:none or is detached from the DOM.
            return;
        }

        const overflows = getParentOverflows(ta);
        const docTop = document.documentElement && document.documentElement.scrollTop; // Needed for Mobile IE (ticket #240)

        ta.style.height = '';
        ta.style.height = (ta.scrollHeight+heightOffset)+'px';

        // used to check if an update is actually necessary on window.resize
        clientWidth = ta.clientWidth;

        // prevents scroll-position jumping
        overflows.forEach(el => {
            el.node.scrollTop = el.scrollTop
        });

        if (docTop) {
            document.documentElement.scrollTop = docTop;
        }
    }

    function update() {
        resize();

        const styleHeight = Math.round(parseFloat(ta.style.height));
        const computed = window.getComputedStyle(ta, null);

        // Using offsetHeight as a replacement for computed.height in IE, because IE does not account use of border-box
        var actualHeight = computed.boxSizing === 'content-box' ? Math.round(parseFloat(computed.height)) : ta.offsetHeight;

        // The actual height not matching the style height (set via the resize method) indicates that
        // the max-height has been exceeded, in which case the overflow should be allowed.
        if (actualHeight < styleHeight) {
            if (computed.overflowY === 'hidden') {
                changeOverflow('scroll');
                resize();
                actualHeight = computed.boxSizing === 'content-box' ? Math.round(parseFloat(window.getComputedStyle(ta, null).height)) : ta.offsetHeight;
            }
        } else {
            // Normally keep overflow set to hidden, to avoid flash of scrollbar as the textarea expands.
            if (computed.overflowY !== 'hidden') {
                changeOverflow('hidden');
                resize();
                actualHeight = computed.boxSizing === 'content-box' ? Math.round(parseFloat(window.getComputedStyle(ta, null).height)) : ta.offsetHeight;
            }
        }

        if (cachedHeight !== actualHeight) {
            cachedHeight = actualHeight;
            const evt = createEvent('autosize:resized');
            try {
                ta.dispatchEvent(evt);
            } catch (err) {
                // Firefox will throw an error on dispatchEvent for a detached element
                // https://bugzilla.mozilla.org/show_bug.cgi?id=889376
            }
        }
    }

    const pageResize = () => {
        if (ta.clientWidth !== clientWidth) {
            update();
        }
    };

    const destroy = (style => {
        window.removeEventListener('resize', pageResize, false);
        ta.removeEventListener('input', update, false);
        ta.removeEventListener('keyup', update, false);
        ta.removeEventListener('autosize:destroy', destroy, false);
        ta.removeEventListener('autosize:update', update, false);

        Object.keys(style).forEach(key => {
            ta.style[key] = style[key];
        });

        map.delete(ta);
    }).bind(ta, {
        height: ta.style.height,
        resize: ta.style.resize,
        overflowY: ta.style.overflowY,
        overflowX: ta.style.overflowX,
        wordWrap: ta.style.wordWrap,
    });

    ta.addEventListener('autosize:destroy', destroy, false);

    // IE9 does not fire onpropertychange or oninput for deletions,
    // so binding to onkeyup to catch most of those events.
    // There is no way that I know of to detect something like 'cut' in IE9.
    if ('onpropertychange' in ta && 'oninput' in ta) {
        ta.addEventListener('keyup', update, false);
    }

    window.addEventListener('resize', pageResize, false);
    ta.addEventListener('input', update, false);
    ta.addEventListener('autosize:update', update, false);
    ta.style.overflowX = 'hidden';
    ta.style.wordWrap = 'break-word';

    map.set(ta, {
        destroy,
        update,
    });

    init();
}

function destroy(ta) {
    const methods = map.get(ta);
    if (methods) {
        methods.destroy();
    }
}

function update(ta) {
    const methods = map.get(ta);
    if (methods) {
        methods.update();
    }
}

let autosize = null;

// Do nothing in Node.js environment and IE8 (or lower)
if (typeof window === 'undefined' || typeof window.getComputedStyle !== 'function') {
    autosize = el => el;
    autosize.destroy = el => el;
    autosize.update = el => el;
} else {
    autosize = (el, options) => {
        if (el) {
            Array.prototype.forEach.call(el.length ? el : [el], x => assign(x, options));
        }
        return el;
    };
    autosize.destroy = el => {
        if (el) {
            Array.prototype.forEach.call(el.length ? el : [el], destroy);
        }
        return el;
    };
    autosize.update = el => {
        if (el) {
            Array.prototype.forEach.call(el.length ? el : [el], update);
        }
        return el;
    };
}


$(function () {
    'use strict';

    $('[data-toggle="offcanvas"]').on('click', function () {
        $('.offcanvas-collapse').toggleClass('open');
        console.log("OPENED");
    })
});

autosize($('textarea'));
/**
 *
 *
 *
 * END OF AUTOSIZE
 *
 *
 * */


function getPosts() {
    let requestUrl;
    if (pageName.localeCompare('postPage') != 0) {
        if (groupId == null && associationId == null) {
            requestUrl = "/users/" + userId + "/posts/allvisible";
        } else if (groupId != null) {
            requestUrl = `/groups/${groupId}/posts`;
        } else {
            requestUrl = `/association/${associationId}/posts`;
        }
    } else {
        requestUrl = `/posts/${postId}/comments`;
    }
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", requestUrl);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        createPosts(this.response);
    };

    xhttp.send();
}

var commentsAllowed = true;

function toggleComments() {
    commentsAllowed = !commentsAllowed;
    document.getElementById("comments-anchor").innerText = "Comments " + ((!commentsAllowed) ? "dis" : "") + "allowed";
}

function send() {
    if (pageName.localeCompare('postPage') != 0) {
        sendPost();
    } else {
        sendComment();
    }
}

function sendComment() {
    if (document.getElementById("post-content-box").value.length > 0) {
        let xhttp = new XMLHttpRequest();
        xhttp.open("POST", "/posts/create/comment");
        xhttp.setRequestHeader('Content-Type', 'application/json');
        xhttp.onload = function () {
            // getPosts();
        };

        let commentData = {
            "postId": postId,
            "message": document.getElementById("post-content-box").value
        };
        xhttp.send(JSON.stringify(commentData));
    }
}

function sendPost() {
    if (document.getElementById("post-content-box").value.length > 0) {
        let xhttp = new XMLHttpRequest();
        xhttp.open("POST", "/posts/create/post");
        xhttp.setRequestHeader('Content-Type', 'application/json');
        xhttp.onload = function () {
            // getPosts();
        };

        let postData = {
            "contents": document.getElementById("post-content-box").value,
            "groupId": groupId,
            "isCommentable": commentsAllowed,
        };
        xhttp.send(JSON.stringify(postData));
    }


}

function createPosts(postArray) {
    if (postArray.length == 0) {
        // no posts to show
        let postBox = document.getElementById('news-feed');
        postBox.classList.add('empty-posts');
        let noPostsElement = document.createElement("p");
        noPostsElement.classList.add("empty-posts-p");
        noPostsElement.innerText = "Hmmmmm.... there doesn't appear to be much activity to show...";
        postBox.appendChild(noPostsElement);
    } else {
        // posts to show
        let postBox = document.getElementById('news-feed');
        postBox.classList.remove('empty-posts');
        let originalChildren = postBox.children.length;
        for (let i = 0; 1 < originalChildren; i++) {
            postBox.removeChild(postBox.children.item(i));
        }

        for (let i = 0; i < postArray.length; i++) {
            postBox.appendChild(createElement(postArray[i]));
        }
    }
}

function createElement(post) {
    let asHtml;
    if (!isSinglePost) {
        let contentsString = post.contents.split("\n").join("<br>");
        let postDate = post.postedOn.split(" ").join(" @ ");
        let commentString = (post.isCommentable) ? `${post.numComments} comments` : "Comments disabled";
        let commentHtml = (post.isCommentable) ? `<a href="/posts/${post.postId}/conversation">${commentString}</a>` : commentString;
        let groupName = (post.groupName == null || pageName.localeCompare('groupPage') === 0) ? "" : ` --> TO --> ${post.groupName}`;
        asHtml = `<div class="media text-muted pt-3">
            <svg class="bd-placeholder-img mr-2 rounded" width="32" height="32" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Placeholder: 32x32"><title>Placeholder</title><rect width="100%" height="100%" fill="#007bff"/><text x="50%" y="50%" fill="#007bff" dy=".3em">32x32</text></svg>
            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                <strong class="d-block text-gray-dark" style="padding-bottom: 5px;">${post.firstName} ${post.lastName}${groupName}</strong>
                ${contentsString}
                
                <small class="d-block text-left mt-3">
                    ${postDate}
                </small>
                <small class="d-block text-right mt-3">
                    ${commentHtml}
                </small>
            </p>
        </div>`;
    } else {
        let contentsString = post.message.split("\n").join("<br>");
        let postDate = post.postedOn.split(" ").join(" @ ");
        asHtml = `<div class="media text-muted pt-3">
            <svg class="bd-placeholder-img mr-2 rounded" width="32" height="32" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Placeholder: 32x32"><title>Placeholder</title><rect width="100%" height="100%" fill="#007bff"/><text x="50%" y="50%" fill="#007bff" dy=".3em">32x32</text></svg>
            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                <strong class="d-block text-gray-dark" style="padding-bottom: 5px;">${post.firstName} ${post.lastName}</strong>
                ${contentsString}
                <small class="d-block text-right mt-3">
                    ${postDate}
                </small>
            </p>
        </div>`;
    }

    return $.parseHTML(asHtml)[0];
}

getPosts();