var allGroupNames = [];
var fromCancel = false;
var groupNameTable = document.getElementById('group-name-table');

function getAllGroupNames() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", "/groups/search/groupnames");
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        for(let i = 0; i < this.response.length; i++) {
            if (!allGroupNames.includes(this.response[i].name)) {
                allGroupNames.push(this.response[i].name);
            }
        }
    };
    xhttp.send();
}

function preventPost() {
    fromCancel = true;
}

function postNewGroup(form) {
    if (fromCancel) {
        fromCancel = false;
        closeGroupPopUp();
        return;
    }
    console.log("POSTING");
    console.log("Group name before posting: " + allGroupNames.toString());
    var formElements = form.elements;
    let formParams = {};
    let valid = true;
    for (let i = 0; i < formElements.length; i += 1) {
        let elem = formElements[i];
        switch (elem.type) {
            case 'submit':
                break;
            default:
                if (elem.name === 'name') {
                    if (allGroupNames.includes(elem.value)) {
                        valid = false;
                        break;
                    }
                    formParams[elem.name] = elem.value;
                } else {
                    formParams[elem.name] = elem.value;
                }
        }

        if (!valid) {
            break;
        }
    }
    if (!valid) {
        // fail
        console.log("Validation failed");
        document.getElementById('group-name-field').classList.add('errorClass');
    } else {
        // exit on success
        closeGroupPopUp();
        document.getElementById('group-name-field').classList.remove('errorClass');
        postNewGroupData(formParams);
    }
}

function createGroupPopUp() {
    document.getElementById("createGroupForm").style.display = "block";
}

function closeGroupPopUp() {
    document.getElementById("createGroupForm").style.display = "none";
}

function postNewGroupData(formData) {
    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "/groups/add/byname");
    xhttp.setRequestHeader('Content-Type', 'application/json');
    xhttp.onload  = function() {
        getAllGroupNames();
        updateGroupTable();
    };
    xhttp.send(JSON.stringify(formData));
}

function updateGroupTable() {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", "/groups/search/byid");
    xhttp.responseType = 'json';
    xhttp.onload  = function() {
        console.log(this.response);
        groupNameTable = document.getElementById('group-name-table');
        // <tr><th scope="row">%ID%</th><th scope="row">%GROUP_NAME%</th></tr>
        let tableBody = groupNameTable.children[1];
        let originalSize = tableBody.children.length;
        for (let i = 0; i < originalSize; i++) {
            tableBody.deleteRow(0);
        }
        for (let i = 0; i < this.response.length; i++) {
            let tableRow = document.createElement('tr');
            let index = document.createElement('th');
            index.setAttribute('scope', 'row');
            index.innerText = (i + 1).toString();
            let nameCol = document.createElement('th');
            nameCol.innerHTML = "<a href=\"/groups/" + this.response[i].id + "\">" + this.response[i].name + "</a>";
            tableRow.appendChild(index);
            tableRow.appendChild(nameCol);
            tableBody.append(tableRow);
        }

    };
    xhttp.send();
}

getAllGroupNames();