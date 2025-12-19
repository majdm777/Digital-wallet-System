
let USER_ID;
let DIVSData = [];

let Balance;
//=localStorage.getItem("Balance")

function send(action, extraData = {}) {
    return fetch("Maindata.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ action, ...extraData })
    })
    .then(res => res.json());
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
        let totalSpent = parseFloat(Data.spend)*(-1.00) || 100 ;
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
        alert("1");
        
        send('CheckUserRequest')
            .then(res =>{
                if(!res.approve){
                    alert("error")
                    return
                }
                alert("2");
                return send("CheckBalance", { money })
            
            }).then(res => {
                    if (!res.approved) {
                        alert("you cant request a withdrawal request while have pending request");
                        return;
                    }
                    placeCashOut(money);
                    cancelPopup();
                }); 
        
        
        
        
        
        
    
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
        return
    })
    
}

function placeCashOut(amount){
    send("CashOut",{ "amount":amount}).then(majd =>{
        alert(majd.comment);
        return;
    })
}

//=================================================================== check point =================================================================== //













// function confirmedOperation(){
//     const amount=document.getElementById("amount-input").value
//     const To=document.getElementById("to-input").value
//     popup.dataset.type="cash-out"
//     let popup=document.getElementById("popup-id");
//     const cashType= popup.dataset.type;
//     const toinfo=document.getElementById("to-input").value
//     // const User_iD=localStorage.getItem("User-ID");


//    if(To){
//      if(To!=="majd-9829" && To!=="name-1212" ){
//         alert("User Not Found")
//         // cancelPopup();
//         return;
//     }
//    }

//     // validate amount
//     if(!amount || parseFloat(amount) <= 0){
//         alert("you need to choose an amount")
//         return;
//     }

//     if(parseFloat(Balance) < parseFloat(amount)){
//         alert("can not place this transaction EROR(0001)")
//         cancelPopup();
//         return;
//     }
//     Balance = parseFloat(Balance) - parseFloat(amount);
//     localStorage.setItem("Balance", Balance);





// // check the type
//     let type;
//     if(String(cashType).toLowerCase().includes('send')){
//         type = "send";
//     } else {
//         type = "received";
//     }

//     //check if there is a sufficient amount
//     if(!amount || amount <=0){
//         alert("you need to choose an amount")
//         return;
//     }

//     // edit the speed amount per/Month
//     let spent=JSON.parse(localStorage.getItem("spent"))|| [0,0,0];
//     spent[1] += parseFloat(amount);
//     spent[0] += parseFloat(amount);
//     spent[2] += parseFloat(amount);

//     localStorage.setItem("spent", JSON.stringify(spent));

//     let Balancee=parseFloat(localStorage.getItem("Balance")) || 0;

//     // let transaction_number=parseFloat(localStorage.getItem("NumberOfTransactions")) || 0;
//     // transaction_number++;
//     // localStorage.setItem("NumberOfTransactions",transaction_number);

//     let TranNum = JSON.parse(localStorage.getItem("TranNum"))|| [0,0,0];
//     TranNum[0]++;
//     TranNum[1]++;
//     TranNum[2]++;
//     let transaction_number = TranNum[0];

//     localStorage.setItem("TranNum", JSON.stringify(TranNum));

//     let transaction_code = GenerateGlobalTransactionCode();

//     const date = new Date().toLocaleDateString();

//     DIVSData.push({USER_ID, cashType, toinfo, date, type, amount, Balancee, transaction_code, transaction_number});
//     localStorage.setItem("operations", JSON.stringify(DIVSData));
//     // create operation in DOM without reloading
//     createOperation(USER_ID, cashType, toinfo, date, type, amount);
//     // persist balance was already updated
//     return;



// }

function loadOperations(){
    
    if(localStorage.getItem("operations")){
        DIVSData=JSON.parse(localStorage.getItem("operations"))
        DIVSData.forEach(Data => createOperation(Data.USER_ID,Data.cashType,Data.toinfo,Data.date,Data.type,Data.amount))
            
    }else{
        //document.querySelector(".rside_user_operations-box").innerHTML=`<p class="emptyMsge">No operations Yet </p>`

        
    }
}
window.onload = function() {
    fillinfo();
    loadOperations();
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
    document.querySelector(".rside_user_operations-box").appendChild(newOperation);
    cancelPopup();
    
    return;

}
function clearOperations(){
    DIVSData.length=0;
    localStorage.removeItem("operations");
    // document.querySelector(".rside_user_operations-box").innerHTML=`<p class="emptyMsge">No operations Yet </p>`

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