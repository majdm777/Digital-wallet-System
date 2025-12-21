let DIVSuser=[]
let USER_ID;
let PERIOD;

function send(action, extraData = {}) {
    return fetch("HistoryDataHandle.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ action, ...extraData })
    })
    .then(res => res.json());
}










function load_His_Info(period){

    send("GetHistoryInfo",{period}).then(Data=>{
        document.getElementById("number_of_tran").innerHTML=Data.transaction_num;
        document.getElementById("Total_spent").innerHTML=parseFloat(Data.total_spent).toFixed(2)+"$"
        document.getElementById("Total_received").innerHTML=parseFloat(Data.total_received).toFixed(2)+"$"
    })


    
if(period == 7){
    PERIOD=7;
    document.getElementById("year").style.opacity="50%"
    document.getElementById("week").style.opacity="100%"
    document.getElementById("month").style.opacity="50%"

    
    
    

}else if(period == 365){
    PERIOD=365
    document.getElementById("year").style.opacity="100%"
    document.getElementById("week").style.opacity="50%"
    document.getElementById("month").style.opacity="50%"


}else if(period == 30){
    PERIOD=30;
    document.getElementById("year").style.opacity="50%"
    document.getElementById("week").style.opacity="50%"
    document.getElementById("month").style.opacity="100%"


}else{
    document.getElementById("year").style.opacity="50%"
    document.getElementById("week").style.opacity="50%"
    document.getElementById("month").style.opacity="50%"
}

}

document.getElementById("year").addEventListener("click",()=>{load_His_Info(365) })
document.getElementById("month").addEventListener("click",()=>{load_His_Info(30) })
document.getElementById("week").addEventListener("click",()=>{load_His_Info(7) })
//===================================================================check point=======================================================================================

function loadUsers() {
    const DIVSData = JSON.parse(localStorage.getItem("operations")) || [];
    if (DIVSData.length === 0) { return; }

    DIVSData.forEach(User => {
        if (User.toinfo) {
        
            if (!DIVSuser.some(u => u.Users === User.toinfo)) {
                DIVSuser.push({ Users: User.toinfo, Date: User.date });
            }
        }
    });
}
function loadUserHistory(){

    send("loadUsers").then(Data=>{
        if(!Data || Data.length==0){
            document.querySelector(".Main_Users_history").innerHTML=`<div class="emptymsge">No History Yet</div>`
            return
        }
        Data.forEach(row=>{
            createUserHistory(row.name,row.first_interaction,row.user_id)
        })
    })




 
}

function createUserHistory(name, Date, ID) {
    const newUser = document.createElement("div");
    newUser.classList.add("Unique_User");

    newUser.innerHTML = `
        <div class="User_info">
            <span class="User_info_upper">${name}-${ID}</span>
            <span class="User_info_upper">${Date}</span>
        </div>
        <div class="unique_user_extended"></div>
    `;

    const userTran = newUser.querySelector(".unique_user_extended");

    send("user-to-user_Info", { ID }).then(Data => {

        if (!Data || Data.length === 0) {
            const newTran = document.createElement("div");
            newTran.classList.add("transactions");
            newTran.innerHTML = `<span class="transaction_info">No transactions recorded</span>`;
            userTran.appendChild(newTran);
            return;
        }

        Data.forEach(elt => {
            const newTran = document.createElement("div");
            newTran.classList.add("transactions");
            newTran.innerHTML = `
                <span class="transaction_info amount">${elt.amount}$</span>
                <span class="transaction_info date">${elt.created_at}</span>
                <span class="transaction_info code">${elt.transfer_id}</span>
                <span class="transaction_info direction">${elt.direction}</span>
            `;
            userTran.appendChild(newTran);
        });

        userTran.style.justifyContent = "start";
    });

    newUser.addEventListener("click", () => {
        newUser.classList.toggle("active");
    });

    document.querySelector(".Main_Users_history").appendChild(newUser);
}


// function remove_Transaction(number,TranDiv) {
//     let DIVSData = JSON.parse(localStorage.getItem("operations")) || [];

    
//     DIVSData = DIVSData.filter(tran => tran.transaction_number !== number);

    
//     localStorage.setItem("operations", JSON.stringify(DIVSData));

    
    
    

//     const parentUser = TranDiv.closest(".Unique_User");
//     const userExtended = parentUser.querySelector(".unique_user_extended");
//     if (TranDiv) TranDiv.remove();
//     if (userExtended.children.length === 0) {
//         userExtended.style.justifyContent="center";
//         userExtended.innerHTML=`<p class="emptymsge" style="color:red">No more transaction</p>`    
//     }
// }



window.onload=function(){
    load_His_Info(30);
    loadUsers();
    loadUserHistory();
}


