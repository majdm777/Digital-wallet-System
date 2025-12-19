// import { clearOperationsa } from './Main.js';

function addinfo(){

    let output=document.getElementsByClassName("get-info")

    
        // send("gentuserInfo").then(res =>{
        //     output[0].innerHTML=res.ID;



        
        // })
    // output[0].innerHTML=localStorage.getItem("First-Name");
    // output[1].innerHTML=localStorage.getItem("Last-Name")
    // output[2].innerHTML=localStorage.getItem("birthdate")
    // output[3].innerText=localStorage.getItem("nationality")
    // output[4].innerHTML=localStorage.getItem("email")
    // output[5].innerHTML=localStorage.getItem("phone")
    // output[6].innerHTML=localStorage.getItem("First-Name")+"-"+localStorage.getItem("User-Id");
    // output[7].innerHTML=localStorage.getItem("Address")
    // output[8].innerHTML=localStorage.getItem("Incomesrc")
    // output[9].innerHTML=localStorage.getItem("Type-Of-Acc")
    // output[10].innerHTML=localStorage.getItem("Purpose");

    const infos = document.querySelectorAll(".get-info");

    infos.forEach(el => {
        if (el.scrollWidth > el.clientWidth) {
            el.style.paddingLeft="100%";
            el.style.animation = "reveal 3s linear infinite";
          }
    });

}
window.onload=addinfo();


const Info=document.querySelectorAll(".changeable")
const input=document.querySelector("#change-input")
let Itemname=""

function cancelPopup(){
    document.getElementById("popup-id").style.display="none";
    document.getElementById("change-input").value="";
     Itemname=""
    field=document.querySelector(".feild");
    field.style.display="block"

   
}
function Popup(){
    popup=document.getElementById("popup-id")
    popup.style.display="flex";
    box1=document.getElementById("popup-box-id")
    box2=document.getElementById("popup-box-id1")
    // field=document.getElementById("change-input");
    // popup.style.display="flex";
    box1.style.display="flex"
    box2.style.display="none"

     
    
}



Info.forEach(change => {
    change.addEventListener("click",()=>{
        const type=change.dataset.type;
        const about=change.dataset.about
        input.type=type
       

        Itemname=about;
        Popup();

    })
});

function confirmedOperation(){
    const input=document.getElementById("change-input")
    let popup=document.getElementById("popup-id");
         
    if(input.type==="tel"){
        const phonePattern = /^[0-9]{8,15}$/;
        if(!phonePattern.test(input.value.trim())){
            alert("wrong phone number")
            return
        }

    }
    




    localStorage.setItem(Itemname,input.value)
    location.reload();
    cancelPopup();
    
    
}


function popupdelete(){
    popup=document.getElementById("popup-id")
    box1=document.getElementById("popup-box-id")
    box2=document.getElementById("popup-box-id1")
    field=document.getElementById("change-input");
    popup.style.display="flex";
    box1.style.display="none"
    box2.style.display="flex"
}    
function confirmedDeletion(){
    localStorage.removeItem("password")
    localStorage.removeItem("First-Name");
    localStorage.removeItem("Last-Name")
    localStorage.removeItem("birthdate")
    localStorage.removeItem("nationality")
    localStorage.removeItem("email")
    localStorage.removeItem("phone")
    localStorage.removeItem("Address")
    localStorage.removeItem("Incomesrc")
    localStorage.removeItem("Type-Of-Acc")
    localStorage.removeItem("Purpose");
    localStorage.removeItem("operations")
    localStorage.removeItem("Balance");
    localStorage.removeItem("Spend")
    localStorage.removeItem("InCome")
    localStorage.removeItem("NumberOfTransactions")
    localStorage.removeItem("spent")
    localStorage.removeItem("received")
    localStorage.removeItem("TranNum")
    localStorage.removeItem("User-Id");






    // clearOperationsa();

    window.location.href="index.html"

        
}
        

// export { cancelPopup, confirmedOperation, popupdelete, confirmedDeletion };
