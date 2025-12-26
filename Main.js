
let USER_ID;
let DIVSData = [];

let Balance;
//=localStorage.getItem("Balance")

function majd(){
    alert("hi")
}




function send(action, extraData = {}) {
    return fetch("Maindata.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ action, ...extraData })
    })
    .then(res => res.json());
}





function logout(){
    // send("logout").then(data =>{
    //     console.log("log out")
    // })
    send("logout").then(data =>{
        if(data.logout){
        window.location.href= "index.php";}
        return;
    })
    window.location.href= "index.php";

}

function fillinfo(){// review after finishing the database
    send("getuserinfo").then(Data =>{



    let input1=document.getElementById("get-id")
    let input2=document.getElementById("get-email");
    let input3=document.querySelector(".amount");
    let input4=document.getElementById("wallet");
    // let input5=document.getElementById("Spend");

    

    // normalize returned keys (expect lowercase from server)
    USER_ID = (Data.fname || Data.Fname) + "-" + (Data.id || Data.ID);

    input1.innerText = USER_ID;
    input2.innerText = Data.email || Data.Email || '';
    input3.innerHTML = parseFloat(Data.balance || 0).toFixed(2) + "$";
    input4.innerText = parseFloat(Data.balance || 0).toFixed(2) + "$";
    // document.getElementById("Spend").innerHTML = "-" + parseFloat(Data.spend || 0).toFixed(2) + "$";
    // input5.innerHTML = "-" + parseFloat(Data.balance || 0).toFixed(2) + "$";
    // set global numeric Balance for later checks
    Balance = parseFloat(Data.balance) || 0;
    })



}
//window.onload=fillinfo();
function showWallet(){
    document.getElementById("popup-id-2").style.display="flex"
}
function showBallance() { 
    document.getElementById("popup-id-3").style.display = "flex";
    
    
    send("getBalance").then(Data => {
        let totalSpent = parseFloat(Data.spend)*(-1.00) || 0 ;
        let totalReceived = parseFloat(Data.received);
        // alert("::"+totalSpent);
        // Update UI
        document.getElementById("Spend").innerHTML = "" + totalSpent.toFixed(2) + "$" ;
        document.getElementById("Income").innerHTML = "+" +  totalReceived.toFixed(2) + "$";
        let Balance = totalSpent + totalReceived;
        let cal = document.getElementById("Ba_lance");
        if (Balance >= 0) {
            cal.style.color = "green";
            cal.innerHTML = "+" + Balance.toFixed(2) + "$";
        } else {
            cal.style.color = "red";
            cal.innerHTML = Balance.toFixed(2) + "$";
           }


    });

}
 

function cancelPopup(){
    document.getElementById("popup-id").style.display="none";
    document.getElementById("popup-id-2").style.display="none";
    document.getElementById("popup-id-3").style.display="none";


    document.getElementById("amount-input").value="";
    document.getElementById("to-input").value="";
}


function PopupByCashSend(){
    popup=document.getElementById("popup-id")
    popup.dataset.type="Cash-Send";
    document.getElementById("to-field").style.display="flex";
    document.getElementById("to-field").value="";
    popup.style.display="flex";
    

}

function PopupByCashOut(){
    popup=document.getElementById("popup-id")
    popup.dataset.type="Cash-Out";
    document.getElementById("to-field").style.display="none";
    document.getElementById("to-field").value="CashOut"

    popup.style.display="flex";
}

//god help me 
function CheckUserValidity() {

    const amount = document.getElementById("amount-input").value;
    const receiver = document.getElementById("to-input").value.trim();
    const money = parseFloat(amount);
    popup=document.getElementById("popup-id")


    if (isNaN(money) || money <= 0) {
        alert("Invalid Amount");
        return;
    }


    // CASE 1: cash-out (receiver empty)
    if (popup.dataset.type === "Cash-Out") {
        
        
        send('CheckUserRequest')
            .then(res => {
            if (!res.approve) {
                alert("You already have a pending withdrawal request");
                throw "stop";
            }
            return send("CheckBalance", { money });
            })
            .then(res => {
            if (!res.approved) {
            alert("Insufficient balance");
            throw "stop";
        }
        placeCashOut(money);
        cancelPopup();
        })
        .catch(()=>{});
        
        return;
    }

    if(receiver === ""){
        alert("please enter user id")
        return;
    }
    
    // CASE 2: cash-send
    send("CheckBalance", { money })
        .then(res => {
            if (!res.approved) {
                alert("Insufficient Amount");
                throw "stop";
            }
            return send("receiverExistence", { receiver });
        })
        .then(res => {
            if (res.exist === -1) {
                alert("user not found");
                throw "stop";
            }
            if (res.exist === 0) {
                alert("you cant send money to yourself");
                throw "stop";
            }
            
            
            placetransaction("Cash-Send", receiver, money);
            cancelPopup();
        })
        .catch(() => {});
}





function placetransaction(Type,receiver,amount){ //function transfering money . review after finishing the database
    
    let transaction_code = parseInt(GenerateGlobalTransactionCode());
    send("transferMoney",{"transaction_code":transaction_code,"type":Type,'receiver_id':receiver,'amount':amount}).then(res =>{

        alert(res.comment);
        
    })
    location.reload();
    return
    
}

function placeCashOut(amount){
    send("CashOut",{ "amount":amount}).then(majd =>{
        alert(majd.comment);
        
    })
    location.reload();
    return;
}



function loadOperations(){

    send("operations").then(Data=>{


        if(!Data || Data.length==0){
            document.querySelector("#user_operations").innerHTML=`<p class="emptyMsge">No operations Yet </p>`
        }else{
            Data.forEach(row => {
                sender_ID=row.sender_name+"-"+row.sender_id;
                receiver_ID=row.receiver_name+"-"+row.receiver_id;
                const dateObj = new Date(row.created_at);
                const formattedDate = 
                (dateObj.getMonth() + 1).toString().padStart(2, '0') + '/' +
                dateObj.getDate().toString().padStart(2, '0') + '/' +
                dateObj.getFullYear(); 

            createOperation(sender_ID,row.Operation,receiver_ID,formattedDate,row.transfer_id,row.amount);
            
            });
        }    
    })



    

}
window.onload = function() {
    fillinfo();
    loadOperations();
    CreateWithdrawalBox();
};



function createOperation(USER_ID,cashType,toinfo,date,type,amount){
    const newOperation =document.createElement("div");
    newOperation.classList.add("operation");
    newOperation.classList.add("glass-small")
    newOperation.innerHTML=`
                    <div class="operation-info">
                        <span class="users-operation-info from-operation-info">${USER_ID}</span>
                        <span class="type-operation-info cash_type">${cashType}</span>
                        <span class="users-operation-info to-operation-info">${toinfo}</span>
                    </div>
                    <div class="operation-info">
                        <span class="users-operation-info date-operation-info">${date}</span>
                        <span class="type-operation-info operation_type">${type}</span>
                        <span class="users-operation-info amount-operation-info">${parseFloat(amount).toFixed(2)}$</span>
                    </div>

    `
    document.querySelector("#user_operations").appendChild(newOperation);
    cancelPopup();
    
    return;

}

function reload(){ 
    location.reload();   
}

function GenerateGlobalTransactionCode(){
    const posCode="0123456789"
    let Code=""
    for (let i=0;i<9;i++){
        Code += posCode.charAt(Math.floor(Math.random() * posCode.length));
    }
    return Code;
}


function CreateWithdrawalBox(){
    const box= document.getElementById("withdrawalRequest")
    
    let amount;
    let withdrawal_id;
    let dateObj
    let formattedDate

    send("GetWithdrawalInfo").then(Data=>{
        if(!Data || Data.length==0){
            box.innerHTML=`<p class="emptyMsge">No Request Yet </p>` 
            return;   
        }else{
            Data.forEach(data=>{
            // Datee =data.created_at|| "2025";
            dateObj = new Date(data.created_at);
            formattedDate = 
            (dateObj.getMonth() + 1).toString().padStart(2, '0') + '/' +
            dateObj.getDate().toString().padStart(2, '0') + '/' +
            dateObj.getFullYear();            
            amount=data.amount|| "0.0";
            withdrawal_id=data.withdrawal_id || "withdrawl id";
            })
        } 
            const newOperation =document.createElement("div");
            newOperation.classList.add("operation");
            newOperation.classList.add("glass-small")
            newOperation.innerHTML=`
                                    <div class="operation-info">
                                        <span class="users-operation-info from-operation-info">${USER_ID}</span>
                                        <span class="type-operation-info cash_type">withdrawal</span>
                                        <span class="users-operation-info to-operation-info" style=" color:red; cursor:pointer" onclick="RemoveRequest()">Remove Request</span>
                                    </div>
                                    <div class="operation-info">
                                        <span class="users-operation-info date-operation-info">${formattedDate}</span>
                                        <span class="type-operation-info operation_type">${withdrawal_id}</span>
                                        <span class="users-operation-info amount-operation-info">${parseFloat(amount).toFixed(2)}$</span>
                                    </div>
                                    `
        box.appendChild(newOperation); 

    })


}



function RemoveRequest(){
    send("removeRequest").then(Data=>{
        alert(Data.comment);
        
    })
    CreateWithdrawalBox();
    return;
}
//=================================================================== check point =================================================================== //
