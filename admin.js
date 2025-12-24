function toggleModal(id) {
    const m = document.getElementById(id);
    m.style.display = m.style.display === 'flex' ? 'none' : 'flex';

    document.getElementById("usernameInput").value="";
    document.getElementById("passwordInput").value="";
    document.getElementById("emailInput").value="";
}



function send(action, extraData = {}) {
    return fetch("adminData.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action, ...extraData })
    }).then(res => res.json());
}


function loadBalance(){
    send("getBalance").then (data=>{
        if(!data){
            return;
        }
        document.getElementById("globalBalance").innerText= data.balance;
    })
}

function loadUsers(){
    send("getUsers").then(data=>{
        if(!data){
            return;
        }
        document.getElementById("totalUsers").innerHTML= data.users;
        
})
}

function loadPendingwithdrawals(){
    send("getWithdrawals").then(data=>{
        if(!data){
            return;
        }
        document.getElementById("pendingCounter").innerHTML= data.withdrawals;
        
})
}

function addManager() {
    let user  = document.getElementById("usernameInput").value;
    let pass  = document.getElementById("passwordInput").value;
    let email = document.getElementById("emailInput").value;

    send("addManager", { user, pass, email }).then(data => {
        if (!data) {
            alert("error adding manager");
            return;
        }

        alert(data.comment);
        toggleModal('addManagerModal');
        showManagerRow(); // refresh list
    });
}

function showManagerRow() {
    send("getManagerRows").then(data => {
        if (!data || data.length === 0) return;

        const container = document.getElementById("managerTableBody");
        container.innerHTML = ""; // clear old rows
        let header= document.createElement("div");
        header.className="manager-row header";
        header.innerHTML=`
                            <div>ID</div>
                            <div>Username</div>
                            <div>Email</div>
                            <div>Performance</div>
                            <div>Status</div>
                            <div>Actions</div>
        `
        container.appendChild(header);



        data.forEach(m => {
            renderManagers(
                m.manager_id,
                m.username,
                m.email,
                m.handled_count,
                m.status
            );
        });
    });
}


function renderManagers(id, username, email, handled_count, status) {
    const container = document.getElementById("managerTableBody");

    const row = document.createElement("div");
    row.className = "manager-row";

    row.innerHTML = `
        <div>${id}</div>
        <div>${username}</div>
        <div>${email}</div>
        <div>${handled_count}</div>
        <div>
            <span class="tag-${status}">${status}</span>
        </div>
        <div>
            <button onclick="invokeManager(${id})">Invoke</button>
        </div>
    `;

    container.appendChild(row);
}


function invokeManager(id) {

    send("invokeManager", { id }).then(data => {
        if (!data) {
            alert("error invoking manager");
            return;
        }

        alert(data.comment);
        showManagerRow(); // refresh list
    } )

}




window.onload=function(){
    loadBalance();
    loadUsers();
    loadPendingwithdrawals();
    showManagerRow();
}
function reloadData() {
    location.reload()
}


