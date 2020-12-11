var possibleUsers = null;
var userInfo = null;
var userMap = {};
var dropDownUsers = [];
var action = null;
var selectedIds = [];
var role = null;
var roles = {
    "groupPage": {
        "MEMBER": 1,
        "ADMIN": 2
    },
    "associationPage": {
        "MEMBER": 3,
        "ADMIN": 2
    }
};
var roleId = null;
var currentMembers = null;
var potentialMembers = null;


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


function getGroupPotentialMembers() {
    let xhttp = new XMLHttpRequest();

    let url = "";
    if (pageName.localeCompare('associationPage') === 0) {
        url = `/association/${associationId}/members/potential`
    } else {
        url = `/groups/${groupId}/members/potential`;
    }

    xhttp.open("GET", url);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        potentialMembers = this.response['potentialMembers'];
    };

    xhttp.send();
}

function getGroupCurrentMembers() {
    let xhttp = new XMLHttpRequest();
    let url = "";
    if (pageName.localeCompare('associationPage') === 0) {
        url = `/association/${associationId}/members`
    } else {
        url = `/groups/${groupId}/members`;
    }

    xhttp.open("GET", url);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        currentMembers = this.response;
    };

    xhttp.send();
}

function getUserInfo() {
    let windowN = 250;
    let userParams = possibleUsers.allUsers.map(uid => `userid[]=${uid}`);
    // batch get user info
    for (let i = 0; i < userParams.length; i += windowN) {
        let xhttp = new XMLHttpRequest();
        let windowEnd = (i + windowN - 1 < userParams.length) ? i + windowN : userParams.length;
        let joinedParams = userParams.slice(i, windowEnd).join("&");
        xhttp.open("GET", `/users?${joinedParams}`);
        xhttp.responseType = 'json';
        xhttp.onload  = function() {
            if (null == userInfo) {
                userInfo = this.response;
            } else {
                userInfo = userInfo.concat(this.response);
            }
            this.response.forEach(usr => {
                userMap[usr['id']] = usr;
            });
        };
        xhttp.send();
    }
}


$('.adm-action-btn').click(function() {
    console.log(this);
    let desc = "";
    if (this.id.localeCompare("add-user-privilege") === 0) {
        desc = "Add user";
        action = "ADD";
        $('.padded-top.selected-role').css('display', '');
        $('.role-selections').css('display', '');
    } else {
        action = "DELETE";
        desc = "Remove user";
        $('.padded-top.selected-role').css('display', 'none');
        $('.role-selections').css('display', 'none');
        roleId = roles[pageName]["MEMBER"];
        dropDownUsers = currentMembers[roles[pageName]["MEMBER"].toString()];
        buildDropDownMenu();
    }

    $("#selected-action-span")[0].innerText = desc;
});

$('.adm-prlg-btn').click(function() {
    let desc = "";
    if (this.id.localeCompare("add-admin-privilege") === 0) {
        role = "ADMIN";
        desc = "Make admin";
        dropDownUsers = currentMembers[roles[pageName]["MEMBER"].toString()];
    } else {
        role = "MEMBER";
        desc = "Make member";
        dropDownUsers = potentialMembers;
    }
    roleId = roles[pageName][role];
    $("#selected-role-span")[0].innerText = desc;
    buildDropDownMenu();
});

$('#modal-reset').click(function() {
    reset();
});

function reset() {
    $('.padded-top.selected-role').css('display', '');
    $('.role-selections').css('display', '');
    $("#selected-action-span")[0].innerText = '';
    $("#selected-role-span")[0].innerText = '';
    action = null;
    role = null;
    roleId = null;
    dropDownUsers = [];
    selectedIds = [];
    $('.dropdown-container .btn-group.dropright .dropdown-menu.scrollable-menu').empty();
    $('.chosen-usr-container .chosen-usr-group').empty();
    update();
}

function buildDropDownMenu() {
    $('.chosen-usr-container .chosen-usr-group').empty();
    $('.dropdown-container .btn-group.dropright .dropdown-menu.scrollable-menu').empty();

    dropDownUsers.forEach(usrId=> {
        if (userMap.hasOwnProperty(usrId)) {
            let formatted = `<a id="usr-${usrId}" class="dropdown-item usr-selector">${userMap[usrId]['firstName']} ${userMap[usrId]['lastName']}</a>`;
            $('.dropdown-container .btn-group.dropright .dropdown-menu.scrollable-menu').append(formatted);
        } else {
            console.log(usrId);
        }
    });

    $('.usr-selector').click(function () {
        let curId = this.id.split('-')[1];
        if (!selectedIds.includes(curId)) {
            selectedIds.push(curId)
            $('.chosen-usr-container .chosen-usr-group').append(
                `<li class="list-group-item">${userMap[curId]['firstName']} ${userMap[curId]['lastName']}</li>`
            );
        }
    });
}


function administerUsers() {
    let xhttp = new XMLHttpRequest();
    let url = "";
    if (pageName.localeCompare('associationPage') === 0) {
        url = `/association/${associationId}/administer`
    } else {
        if (action.localeCompare("DELETE") === 0) {
            url = `/groups/${groupId}/removeusers`;
        }
        else {
            url = `/groups/${groupId}/administer`;
        }
    }

    xhttp.open("POST", url);
    xhttp.setRequestHeader('Content-Type', 'application/json');
    xhttp.onload  = function() {

    };

    let payload = {
        "userIds": selectedIds,
        "role": roleId
    };

    xhttp.send(JSON.stringify(payload));
    reset();
}


function update() {
    var currentMembers = null;
    var potentialMembers = null;
    var possibleUsers = null;
    var userInfo = null;
    var userMap = {};

    getUserConnections();
    if (pageName.localeCompare("groupPage") === 0) {
        getGroupPotentialMembers();
    }
    getGroupCurrentMembers();
}




update();