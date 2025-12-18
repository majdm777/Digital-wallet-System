let CURRENT_FIELD = "";

function send(action, extraData = {}) {
    return fetch("Account.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action, ...extraData })
    }).then(res => res.json());
}

/*LOAD ACCOUNT DATA*/

function loadAccountInfo() {
    send("getAccountInfo").then(data => {
        const out = document.getElementsByClassName("get-info");

        out[0].innerText = data.FirstName;
        out[1].innerText = data.LastName;
        out[2].innerText = data.Birthday;
        out[3].innerText = data.Nationality;
        out[4].innerText = data.Email;
        out[5].innerText = data.Phone;
        out[6].innerText = data.FirstName + "-" + data.user_id;
        out[7].innerText = data.Address;
        out[8].innerText = data.Income_source;
        out[9].innerText = data.Type_Of_Account;
    });
}

window.onload = loadAccountInfo;

/*POPUP CONTROL*/

function Popup() {
    document.getElementById("popup-id").style.display = "flex";
    document.getElementById("popup-box-id").style.display = "flex";
    document.getElementById("popup-box-id1").style.display = "none";
}

function cancelPopup() {
    document.getElementById("popup-id").style.display = "none";
    document.getElementById("change-input").value = "";
    CURRENT_FIELD = "";
}

/*CLICKABLE FIELDS*/

document.querySelectorAll(".changeable").forEach(el => {
    el.addEventListener("click", () => {
        CURRENT_FIELD = el.dataset.about;
        document.getElementById("change-input").type = el.dataset.type;
        Popup();
    });
});

/*CONFIRM UPDATE*/

function confirmedOperation() {
    const value = document.getElementById("change-input").value.trim();

    send("updateField", {
        field: CURRENT_FIELD,
        value: value
    }).then(res => {
        if (!res.success) {
            alert(res.msg || "Update failed");
            return;
        }
        location.reload();
    });
}

/*DELETE ACCOUNT*/

function popupdelete() {
    document.getElementById("popup-id").style.display = "flex";
    document.getElementById("popup-box-id").style.display = "none";
    document.getElementById("popup-box-id1").style.display = "flex";
}

function confirmedDeletion() {
    send("deleteAccount").then(() => {
        window.location.href = "index.html";
    });
}
