
function DeleteTransactions(){
    DIVSData.length=0;
    localStorage.removeItem("operations");
    location.reload();
    
}


function LoadTransactions(){
    let DIVSData=JSON.parse(localStorage.getItem("operations")) || []
    if(DIVSData.length!==0){
        
        DIVSData.forEach(Data => {

            let wallet=parseFloat(Data.Balancee)+ parseFloat(Data.amount);
            createTransaction(Data.USER_ID,Data.cashType,Data.toinfo,Data.date,Data.type,Data.amount,wallet,Data.Balancee,Data.transaction_code,Data.transaction_number)
    })
          
    }else{
        let emptymsg=document.querySelector(".transactions-box")
        emptymsg.innerHTML=`<p class="emptymsge">No Transaction Yet</p>`
    }    
}

window.onload=function(){
    LoadTransactions();
}





function createTransaction(USER_ID,cashType,toinfo,date,type,amount,wallet,Balancee,transaction_code,transaction_number){
    const NewTran=document.createElement("div");
    NewTran.classList.add("Transaction-box");
    NewTran.innerHTML=`
                <div class="transaction-In">
                    <div class="From-To Transaction-In-All">
                        <div class="From-tran" id="From">${USER_ID}</div>
                        <div class="tran-icon" id="icon"><img src="IMAGES/transaction.png" width="30px" height="30px"></div>
                        <div class="To-tran" id="To">${toinfo}</div>
                    </div>
                    <div class="Type Transaction-In-All"> ${cashType}</div>
                    <div class="Amount Transaction-In-All"> ${parseFloat(amount).toFixed(2)}$</div>
                    <div class="Tran_code Transaction-In-All">-${transaction_number}-</div>
                </div>
                
                <div class="Transaction-Out">
                    
                    <div class="Tran-Balance Transaction-Out-All">
                        
                        <div class="balance-info-row"><span class="B1" id="wallet">${parseFloat(wallet).toFixed(2)}$</span></div>
                        <div class="balance-info-row"><span class="B1" id="Spend">${parseFloat(amount).toFixed(2)}$</span></div>
                        
                        <hr style="width: 50%;">
                        <div class="balance-info-row">Remain: <span class="B1" id="remain">${parseFloat(Balancee).toFixed(2)}</span></div>
                        
                    </div>

                    <div class="Users Transaction-Out-All">

                        <div class="User-box-info">
                            <span class="User-Id" id="From-Id">${USER_ID}</span>
                            <span class="User-email" id="From-gmail"></span>
                        </div>

                        <div class="Tran-type">${cashType}</div>

                        <div class="User-box-info">
                            <span class="User-Id" id="To-Id">${toinfo}</span>
                            <span class="User-email" id="To-gmail"></span>
                        </div>

                    </div>

                    <div class="Tran-Info Transaction-Out-All">

                        <div class="infos" id="Date">${date}</div>

                        <div class="infos" id="Type">${type}</div>

                        <div class="infos" id="tran-code">${transaction_code}</div>

                        <div class="infos" id="tran-number">-${transaction_number}-</div>
                        <div class="remove_transaction">remove transaction</div>
                    </div>
`
NewTran.setAttribute("Tran_Num",transaction_number)
NewTran.querySelector(".remove_transaction").addEventListener("click",(e)=>{
    e.stopPropagation();

    remove_Transaction(transaction_number);
    return;
})

NewTran.addEventListener("click", () => {
         NewTran.classList.toggle("active");
       
});
document.querySelector(".transactions-box").appendChild(NewTran);
return;

}





function remove_Transaction(number) {

    let DIVSData=JSON.parse(localStorage.getItem("operations")) || []

    DIVSData = DIVSData.filter(tran => tran.transaction_number !== number);

    
    localStorage.setItem("operations", JSON.stringify(DIVSData));

    
    const tranDiv = document.querySelector(`.Transaction-box[Tran_Num="${number}"]`);
    if (tranDiv) tranDiv.remove();

    if(DIVSData.length===0){
        let emptymsg=document.querySelector(".transactions-box")
        emptymsg.innerHTML=`<p class="emptymsge">No Transaction Yet</p>`
    }
}





// const cards = document.querySelectorAll(".Transaction-box");

// cards.forEach(card => {
//     card.addEventListener("click", () => {
//         card.classList.toggle("active");
//         // alert("hi")
//     });
// });