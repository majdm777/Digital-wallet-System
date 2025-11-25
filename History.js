let DIVSuser=[]


function load_His_Info(period){
    let spent = JSON.parse(localStorage.getItem("spent")) || [0,0,0];
    let received=JSON.parse(localStorage.getItem("received"))|| [0,0,0]
    let TranNum=JSON.parse(localStorage.getItem("TranNum"))|| [0,0,0]

    
if(period==="week"){
    document.getElementById("year").style.opacity="50%"
    document.getElementById("week").style.opacity="100%"
    document.getElementById("month").style.opacity="50%"
    document.getElementById("number_of_tran").innerHTML=TranNum[2];
    document.getElementById("Total_spent").innerHTML=parseFloat(spent[2]).toFixed(2)+"$"
    document.getElementById("Total_received").innerHTML=parseFloat(received[2]).toFixed(2)+"$"

}else if(period==="year"){
    document.getElementById("year").style.opacity="100%"
    document.getElementById("week").style.opacity="50%"
    document.getElementById("month").style.opacity="50%"

    document.getElementById("number_of_tran").innerHTML=TranNum[0];
    document.getElementById("Total_spent").innerHTML=parseFloat(spent[0]).toFixed(2)+"$"
    document.getElementById("Total_received").innerHTML=parseFloat(received[0]).toFixed(2)+"$"
}else{
    document.getElementById("year").style.opacity="50%"
    document.getElementById("week").style.opacity="50%"
    document.getElementById("month").style.opacity="100%"

    document.getElementById("number_of_tran").innerHTML=TranNum[1];
    document.getElementById("Total_spent").innerHTML=parseFloat(spent[1]).toFixed(2)+"$"
    document.getElementById("Total_received").innerHTML=parseFloat(received[1]).toFixed(2)+"$"
}

}

document.getElementById("year").addEventListener("click",()=>{load_His_Info("year") })
document.getElementById("month").addEventListener("click",()=>{load_His_Info("month") })
document.getElementById("week").addEventListener("click",()=>{load_His_Info("week") })

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
    if(DIVSuser.length===0){
        document.querySelector(".Main_Users_history").innerHTML=`<div class="emptymsge">No History Yet</div>`
    }
    DIVSuser.forEach(el =>{
        createUserHistory(el.Users,el.Date)
    })
}

function createUserHistory(User_Id,Date){
    const newUser=document.createElement("div")
    newUser.classList.add("Unique_User")
    newUser.innerHTML=`<div class="User_info">
                    <span class="User_info_upper" id="User_id">${User_Id}</span>
                    <span class="User_info_upper" id="User_date">${Date}</span>

                </div>
                <div class="unique_user_extended"></div>`
    let userTran=newUser.querySelector(".unique_user_extended")

    const DIVSData = JSON.parse(localStorage.getItem("operations")) || [];
    DIVSData.forEach(elt => {
        if(elt.toinfo === User_Id){
            const newTran=document.createElement("div")
            newTran.classList.add("transactions")
            newTran.innerHTML=`
                        <span class="transaction_info" id="amount" >${parseFloat(elt.amount).toFixed(2)}$</span>
                        <span class="transaction_info" id="Date">${elt.date}</span>
                        <span class="transaction_info" id="TranCode">${elt.transaction_code}</span>
                        <span class="transaction_info" id="TranNum">${elt.transaction_number}</span>
                        <span class="Remove-button" >Remove</span>

                        `

            newTran.setAttribute("Tran_Num",elt.transaction_number)
            newTran.querySelector(".Remove-button").addEventListener("click",(e)=>{
                e.stopPropagation();

                remove_Transaction(elt.transaction_number,newTran);
                return;
            })
            userTran.style.justifyContent="start";
            userTran.appendChild(newTran)
        }
    })

    
    
    newUser.addEventListener("click", () => {
         newUser.classList.toggle("active");
       
    });
    document.querySelector(".Main_Users_history").appendChild(newUser)
}

function remove_Transaction(number,TranDiv) {
    let DIVSData = JSON.parse(localStorage.getItem("operations")) || [];

    
    DIVSData = DIVSData.filter(tran => tran.transaction_number !== number);

    
    localStorage.setItem("operations", JSON.stringify(DIVSData));

    
    
    

    const parentUser = TranDiv.closest(".Unique_User");
    const userExtended = parentUser.querySelector(".unique_user_extended");
    if (TranDiv) TranDiv.remove();
    if (userExtended.children.length === 0) {
        userExtended.style.justifyContent="center";
        userExtended.innerHTML=`<p class="emptymsge" style="color:red">No more transaction</p>`    
    }
}



window.onload=function(){
    load_His_Info("");
    loadUsers();
    loadUserHistory();
}


