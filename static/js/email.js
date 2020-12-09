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

var possibleUsers = null;
var sentEmails = null;
var receivedEmails = null;
var userInfo = null;
var isReading = true;
var associations = null;
var groups = null;
var dropdownBuilt = false;
var recipients = [];
var recipientDetails = [];

var currentEmails = null;

var subjectTextArea = `<div id="textarea-padding">
            <textarea id="subject-content-box" placeholder="Enter a subject..." maxlength="255"></textarea>
        </div>`;

var emailTextArea = `<div id="textarea-padding">
            <textarea id="email-content-box" placeholder="Your email goes here"></textarea>
        </div>
        <div id="send-email-div">
            <button onclick="sendEmail()" type="button" class="btn btn-primary" data-toggle="button" aria-pressed="false" autocomplete="off">
            Send!
            </button>
        </div>`;

var defaultMsgSubject = "Weekly Update - Week 19 (May 8, 2017 - May 14, 2017)";

$(".email-option").click(function() {
    $('li.email-option.active')[0].classList.remove('active');
    this.classList.add('active');
    if (this.id.localeCompare('received-emails-inbox') === 0) {
        buildEmailList(receivedEmails);
        currentEmails = receivedEmails;
    } else if (this.id.localeCompare('sent-emails-outbox') === 0) {
        buildEmailList(sentEmails);
        currentEmails = sentEmails;
    }
});

function setActiveEmail(obj) {
    if (!isReading) {
        isReading = true;
        // gotta add the subject back!
        $('.message-body .sender-details .details #textarea-padding').remove();
        $('.message-body .sender-details .details').prepend("<p class=\"msg-subject\"></p>");
        $('#add-user-icon-div').css('display', 'none');
        $('.recipient-group').css('display', 'none');
        $('.recipient-group').empty();
        recipients = [];
    }
    $('.message-body .sender-details .details .msg-subject')[0].innerHTML = defaultMsgSubject;
    if ($('.mail-list.new_mail').length > 0) {
        $('.mail-list.new_mail')[0].classList.remove('new_mail');
    }
    obj.classList.add('new_mail');
    $('.message-body').children('.message-content')[0].innerHTML = currentEmails[obj.id.split('-')[1]]['content'];
}

$(".mail-list").click(function() {
    setActiveEmail(this)
});

$("li.compose").click(function() {
    prepareCompose();
});

$('#add-user-icon-div').click(function() {
    if (!dropdownBuilt) {
        dropdownBuilt = true;
        buildDropDownMenu();
    }
});



function prepareCompose() {
    console.log('clickedComposed');
    if (isReading) {
        isReading = false;
        $('.message-body .sender-details .details .msg-subject').remove();
        $('.message-body .message-content').empty();
        $('.message-body .sender-details .details').prepend(subjectTextArea);
        $('.message-body .message-content').append(emailTextArea);
        $('#add-user-icon-div').css('display', 'flex');
        $('.recipient-group').css('display', 'flex');
        $('.sender-email').css('display', 'none');
    } else {
        $('#subject-content-box').val('');
        $('#email-content-box').val('');
        $('.recipient-group').empty();
        recipients = [];
    }
}



function getUserConnections() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/users/${userId}/connections`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        possibleUsers = this.response;
        getUserInfo();
    };

    xhttp.send();
}

function getAssociations() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/association/get/byid`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        console.log(this.response);
        associations = this.response;
        buildAssociationOptions();
    };
    xhttp.send();
}

function getGroups() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/groups/search/byid`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        groups = this.response;
        buildGroupOptions();
    };

    xhttp.send();
}

function buildAssociationOptions() {
    for (let i = 0; i < associations.length; i++) {
        let formattedButton = `<button id="assn-${associations[i].id}" type="button" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-home-city-outline text-primary mr-1"></i>${associations[i].name}</button>`;
        $('#association-buttons .btn-group-vertical').append(formattedButton);
    }
}

function buildGroupOptions() {
    for (let i = 0; i < groups.length; i++) {
        let formattedButton = `<button id="grp-${groups[i].id}" type="button" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-tablet-ipad text-primary mr-1"></i>${groups[i].name}</button>`;
        $('#group-buttons .btn-group-vertical').append(formattedButton);
    }
}

function sendEmail() {
    let emailObj = {};
    emailObj['subject'] = document.getElementById("subject-content-box").value;
    emailObj['content'] = document.getElementById("email-content-box").value;
    emailObj['recipients'] = recipients;
    console.log(emailObj);

    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "/email");
    xhttp.setRequestHeader('Content-Type', 'application/json');
    xhttp.onload = function () {
        console.log("SENT!");
    };

    xhttp.send(JSON.stringify(emailObj));

    $('#subject-content-box').val('');
    $('#email-content-box').val('');
}

function getSentEmails() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/users/${userId}/emails/sent`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        sentEmails = processEmails(this.response);
    };

    xhttp.send();
}

function getReceivedEmails() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `users/${userId}/emails/received`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        receivedEmails = processEmails(this.response);
    };

    xhttp.send();
}

function processEmails(dbResponse) {
    let emailObj = {};
    let maxPreviewChars = 50;
    for (let i = 0; i < dbResponse.length; i++) {
        let email = dbResponse[i];
        let previewCharNum = (email.content.length > maxPreviewChars) ? maxPreviewChars : email.content.length;
        email['preview'] = email['content'].substring(0, previewCharNum);
        email.content = email.content.split('\n').join('<br>');
        emailObj[email.emailId] = email;
    }
    return emailObj;
}

function getUserInfo() {
    let windowN = 250;
    let userParams = possibleUsers.allUsers.map(uid => `userid[]=${uid}`);
    // batch get user info
    for (let i = 0; i < userParams.length; i += windowN) {
        let xhttp = new XMLHttpRequest();
        let windowEnd = (i + windowN - 1 < userParams.length) ? i + windowN - 1 : userParams.length;
        let joinedParams = userParams.slice(i, windowEnd).join("&");
        xhttp.open("GET", `users?${joinedParams}`);
        xhttp.responseType = 'json';
        xhttp.onload  = function() {
            if (null == userInfo) {
                userInfo = this.response;
            } else {
                userInfo = userInfo.concat(this.response);
            }
        };
        xhttp.send();
    }
}

function buildDropDownMenu() {

    userInfo.forEach(userInf=> {
        let formatted = `<a id="usr-${userInf.id}" class="dropdown-item recipient-selector">${userInf.firstName} ${userInf.lastName}</a>`;
        $('.dropdown-container .btn-group.dropright .dropdown-menu').append(formatted);
    });

    $('.recipient-selector').click(function () {
        if (!recipients.includes(this.id.split('-')[1])) {
            recipients.push(this.id.split('-')[1])
        }
    });
}

function saveSelectedRecipients() {
    recipients.forEach(uid => {
        console.log("looking for " + uid);
        for (let usrInf of userInfo) {
            if (usrInf['id'].localeCompare(uid) === 0) {
                $('.recipient-group').append(`<li class="list-group-item">${usrInf['firstName']} ${usrInf['lastName']}</li>`);
                break;
            }
        }
    });
}

function buildEmailList(emails) {
    $('div.mail-list-container .mail-list').remove();

    let emailKeys = [];
    for (var key in emails) {
        if (!emailKeys.includes(parseInt(key))) {
            emailKeys.push(parseInt(key));
        }
    }

    emailKeys.sort(function(a, b){return b-a});

    emailKeys.forEach(key => {
        let keyS = key.toString();
        if (emails.hasOwnProperty(keyS)) {
            let format = `<div class="mail-list" id="emailid-${emails[keyS].emailId}">
                        <div class="form-check"> <label class="form-check-label"> <input type="checkbox" class="form-check-input"> <i class="input-helper"></i></label></div>
                        <div class="content">
                            <p class="sender-name">${emails[keyS].firstName} ${emails[keyS].lastName}</p>
                            <p class="message_text">${emails[keyS].preview}</p>
                        </div>
                        <div class="details">
                            <i class="mdi mdi-star-outline"></i>
                        </div>
                    </div>`;

            $('div.mail-list-container').append(format);
        }
    });

    $(".mail-list").click(function() {
        setActiveEmail(this)
    });
}





getUserConnections();
getSentEmails();
getReceivedEmails();
getAssociations();
getGroups();
// buildDropDownMenu();