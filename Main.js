
let USER_ID;
let DIVSData = [];

let Balance=localStorage.getItem("Balance")
function fillinfo(){
    let input1=document.getElementById("get-id")
    let input2=document.getElementById("get-email");
    let input3=document.querySelector(".amount");
    let input4=document.getElementById("wallet")
    

    const Username=localStorage.getItem("First-Name")
    const Userid=localStorage.getItem("User-Id");
    const Useremail=localStorage.getItem("email");
    USER_ID=Username+"-"+Userid;


    input1.innerText=USER_ID;
    input2.innerText=Useremail;
    input3.innerHTML=parseFloat(Balance).toFixed(2) + "$"
    input4.innerText=parseFloat(Balance).toFixed(2) + "$"
}
//window.onload=fillinfo();
function showWallet(){
    document.getElementById("popup-id-2").style.display="flex"
}
function showBallance(){
    document.getElementById("popup-id-3").style.display="flex"
    // let spend=localStorage.getItem("Spend") || 0;

    let spent=JSON.parse(localStorage.getItem("spent"))|| [0,0,0];
    let received=JSON.parse(localStorage.getItem("received"))|| [0,0,0];


    // let Income=localStorage.getItem("InCome")|| 0;
    document.getElementById("Spend").innerHTML="-"+parseFloat(spent[1]).toFixed(2)+"$"
    document.getElementById("Income").innerHTML="+"+parseFloat(received[1]).toFixed(2)+"$"
    let Balance = parseFloat(received[1]).toFixed(2) - parseFloat(spent[1]).toFixed(2)
    let cal = document.getElementById("Ba_lance")
    if(Balance >=0){
        cal.style.color="green"
        cal.innerHTML="+"+parseFloat(Balance).toFixed(3)+"$"
    
    }else{
        cal.style.color="red"
        cal.innerHTML=parseFloat(Balance).toFixed(3)+"$"
    }


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
    popup.style.display="flex";

}

function PopupByCashOut(){
    popup=document.getElementById("popup-id")
    popup.style.display="flex";
    popup.dataset.type="Cash-Out"
    document.getElementById("to-field").style.display="none";
}

function confirmedOperation(){
    const amount=document.getElementById("amount-input").value
    const To=document.getElementById("to-input").value

    let popup=document.getElementById("popup-id");
    const cashType= popup.dataset.type;
    const toinfo=document.getElementById("to-input").value
    // const User_iD=localStorage.getItem("User-ID");


   if(To){
     if(To!=="majd-9829" && To!=="name-1212" ){
        alert("User Not Found")
        // cancelPopup();
        return;
    }
   }

    if(parseFloat(Balance)<parseFloat(amount)){
        alert("can not place this transaction EROR(0001)")
        cancelPopup();
        return;
    }
    Balance=parseFloat(Balance)-parseFloat(amount)
    localStorage.setItem("Balance",Balance)





// check the type
    let type;
    if(cashType==="CashSend"){
        type="send";
    }else type="received ";

    //check if there is a sufficient amount
    if(!amount || amount <=0){
        alert("you need to choose an amount")
        return;
    }

    // edit the speed amount per/Month
    let spent=JSON.parse(localStorage.getItem("spent"))|| [0,0,0];
    spent[1] += parseFloat(amount);
    spent[0] += parseFloat(amount);
    spent[2] += parseFloat(amount);

    localStorage.setItem("spent", JSON.stringify(spent));

    let Balancee=parseFloat(localStorage.getItem("Balance")) || 0;

    // let transaction_number=parseFloat(localStorage.getItem("NumberOfTransactions")) || 0;
    // transaction_number++;
    // localStorage.setItem("NumberOfTransactions",transaction_number);

    let TranNum=JSON.parse(localStorage.getItem("TranNum"))|| [0,0,0]
    TranNum[0]++
    TranNum[1]++
    TranNum[2]++;
    let transaction_number=TranNum[0]

    localStorage.setItem("TranNum", JSON.stringify(TranNum));



    let transaction_code=GenerateGlobalTransactionCode();


    const date= new Date().toLocaleDateString();

    DIVSData.push({USER_ID,cashType,toinfo,date,type,amount,Balancee,transaction_code,transaction_number});
    localStorage.setItem("operations",JSON.stringify(DIVSData));
    location.reload();
    createOperation(USER_ID,cashType,toinfo,date,type,amount);



}

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
                        <span class="users-operation-info" id="from-operation-info">${USER_ID}</span>
                        <span class="type-operation-info" id="cash_type" >${cashType}</span>
                        <span class="users-operation-info" id="to-operation-info">${toinfo}</span>
                    </div>
                    <div class="operation-info">
                        <span class="users-operation-info" id="date-operation-info">${date}</span>
                        <span class="type-operation-info" id="operation_type">${type}</span>
                        <span class="users-operation-info" id="amount-operation-info">${parseFloat(amount).toFixed(2)}$</span>
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
    const posCode="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
    let Code=""
    for( i=0;i<12;i++){
        Code += posCode.charAt(Math.floor(Math.random() * posCode.length));
    }
    return Code;
}