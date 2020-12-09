var administeringAssociations = [];
var associationInfo  = [];

var usersCondos = [];
var assnCondos = null;
var assnUsers = null;
var userInfo = null;

var userTableBuilt = false;

var currentElement = $('#dashboard-container');
var currentNavBarId = "dashboard-link";

$('li.admin-nav').click(function() {
    $('li.admin-nav .nav-link.active')[0].classList.remove('active');
    this.children[0].classList.add('active');

    if (this.id !== currentNavBarId) {
        currentNavBarId = this.id;
        if (currentElement !== null) {
            currentElement.css('display', 'none');
        }
        switch (this.id) {
            case "dashboard-link":
                currentElement = $('#dashboard-container');
                break;
            case "user-administration-link":
                if (administeringAssociations.length > 0) {
                    if (!userTableBuilt) {
                        userTableBuilt = true;
                        buildUserTable();
                    }
                    currentElement = $('#user-administration-container');
                }
                break;
            case "condo-administration-link":
                if (administeringAssociations.length > 0) {
                    currentElement = $('#condo-administration-container');
                }
                break;
            case "assn-administration-link":
                if (administeringAssociations.length > 0) {
                    currentElement = $('#assn-administration-container');
                }
                break;
            case "orders-link":
                currentElement = $('#your-condos-container');
                break;
            case "product-link":
            default:
                currentElement = null;
                break;
        }
    }
    if (currentElement !== null) {
        console.log("HERE");
        currentElement.css('display', '');
    }

});

function getAdministeredAssociations() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/users/${userId}/associations/administered`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        console.log(this.response);
        if (this.response.length > 0) {
            administeringAssociations = this.response;
            getAssociationsInfo();
        }
    };
    xhttp.send();
}

function getAssociationsInfo() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/association/get/byid?extrainfo=true`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        associationInfo = this.response;
        if (associationInfo !== null) {
            buildAssociationTable();
            getCondoInfo();
            getUserConnections();
        }
    };
    xhttp.send();
}


function getUserConnections() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", `/users/${userId}/connections`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        for (let key in this.response['usersByAssociation']) {
            if (this.response['usersByAssociation'].hasOwnProperty(key)) {
                if (assnUsers === null) {
                    assnUsers = this.response['usersByAssociation'][key];
                } else {
                    assnUsers = assnUsers.concat(this.response['usersByAssociation'][key].filter(usrId => !assnUsers.includes(usrId)));
                }
            }
        }
        getUserInfo();
    };
    xhttp.send();
}

function getUserInfo() {
    let windowN = 250;
    let userParams = assnUsers.map(uid => `userid[]=${uid}`);
    // batch get user info
    for (let i = 0; i < userParams.length; i += windowN) {
        let xhttp = new XMLHttpRequest();
        let windowEnd = (i + windowN - 1 < userParams.length) ? i + windowN: userParams.length;
        let joinedParams = userParams.slice(i, windowEnd).join("&");
        xhttp.open("GET", `/users?extrainfo=true&${joinedParams}`);
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


function getCondoInfo() {
    let xhttp = new XMLHttpRequest();

    xhttp.open("GET", `/condos?${administeringAssociations.map(val => 'associationid[]=' + val).join("&")}`);
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        assnCondos = this.response;
        if (associationInfo !== null) {
            buildCondoTable();
        }
    };
    xhttp.send();
}


function buildCondoTable() {
    let counter = 1;
    assnCondos.forEach(condo => {
        if (condo['ownerId'] === userId) {
            usersCondos.push(condo);
        }

        let formatted = `<tr>
            <td>${counter}</td>
            <td>${condo['id']}</td>
            <td>${condo['buildingId']}</td>
            <td>${condo['name']}</td>
            <td>${condo['ownerId'] == null ? "----" : condo['ownerId']}</td>
            <td>${condo['floor']}</td>
            <td>${condo['doorNum']}</td>
            <td>NULL</td>
            <td>NULL</td>
            <td>0</td>
            <td>0</td>
        </tr>`;
        $('#condo-table tbody').append(formatted);
        counter++;
    });

    buildUsersCondoTable();
}


function buildUsersCondoTable() {
    let counter = 1;
    usersCondos.forEach(condo => {
        let formatted = `<tr>
            <td>${counter}</td>
            <td>${condo['id']}</td>
            <td>${condo['buildingId']}</td>
            <td>${condo['name']}</td>
            <td>${condo['floor']}</td>
            <td>${condo['doorNum']}</td>
            <td>NULL</td>
            <td>NULL</td>
            <td>0</td>
            <td>0</td>
        </tr>`;
        $('#user-condo-table tbody').append(formatted);
        counter++;
    })
}


function buildUserTable() {
    let counter = 1;
    userInfo.forEach(usrInf => {
        let formatted = `<tr> 
            <td>${counter}</td>
            <td>${usrInf['id']}</td>
            <td>${usrInf['firstName']}</td>
            <td>${usrInf['lastName']}</td>
            <td>${usrInf['email']}</td>
            <td>${usrInf['isActive']}</td>
            <td>${usrInf['numUnits']}</td>
            <td>${usrInf['createdOn']}</td>
        </tr>`;
        $('#user-table tbody').append(formatted);
        counter++;
    });
}


function buildAssociationTable() {
    let counter = 1;
    associationInfo.forEach(assInf => {
        let formatted = `<tr>
            <td>${counter}</td>
            <td>${assInf['id']}</td>
            <td>${assInf['name']}</td>
            <td>${assInf['createdOn']}</td>
            <td>${assInf['numBuildings']}</td>
            <td>${assInf['numUnits']}</td>
            <td>${assInf['numUsers']}</td>
        </tr>`;
        $('#assn-table tbody').append(formatted);
        counter++;
    });
}






// start
getAdministeredAssociations();