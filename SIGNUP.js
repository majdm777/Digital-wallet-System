let before=document.getElementById("page1");
let after=document.getElementById("page2");
let message=document.getElementById("display-message")
let Fname=document.getElementById("First-Name")
let Lname=document.getElementById("Last-Name")
let birthdate=document.getElementById("Date-Of-Birth")
let nationality=document.getElementById("Nationality")
let email=document.getElementById("Email")
let phone=document.getElementById("phone")
let pass=document.getElementById("password")
let copass=document.getElementById("con_password")





function NextPage(){
    
    

    if(Fname.value === ""){ message.innerHTML="please fill the First-Name section";return;}
    if(Lname.value===""){message.innerHTML="please fill the Last-Name section";return}
    if(birthdate.value===""){message.innerHTML="please fill the Date-Of-Birth section";return; 
    }else if(validdate()==0){message.innerHTML="Invalid Date ";return;} 

    if(nationality.value===""){message.innerHTML="please fill the Nationality section";return;}
    if(email.value===""){message.innerHTML="please fill the Email section";return;}//should be improved
    if(phone.value===""){message.innerHTML="please fill the Phone section";return;}
    else if(phone.value.length<8){message.innerHTML="please enter a valid phone number";return;} 
    if(pass.value===""){message.innerHTML="please fill the password section";return;}
    else if(pass.value.length<8){message.innerHTML="please enter a password contain 8 characters or more";return;}
    if(copass.value!=pass.value){message.innerHTML="the passwords does not match \n please make sure they are the same";return;}  
    
    
    
    
    
    message.innerHTML=""
    before.style.display="none"
    after.style.display="block"
}
function validdate(){
    let birth=document.getElementById("Date-Of-Birth").value

    const today= new Date();
    const birthdate= new Date(birth);
    let age = today.getFullYear()-birthdate.getFullYear()
    
    if(age<18 || age>100){
        return 0;
    }return 1;
}    

function Back(){
    let before=document.getElementById("page1");
    let after=document.getElementById("page2");

    before.style.display="block";
    after.style.display="none";
}

function generateID(min,max){
   return Math.floor(Math.random() * (max-min+1))+min;
}

function LastPage(){
    let Next=document.getElementById("Next_button")
    let signedup=document.getElementById("signedup")
    let message=document.getElementById("display-message-2")
    let Address=document.getElementById("address")
    let Incomesrc=document.getElementById("Income-Source")
    let typeofacc=document.getElementById("type-of-account")
    // let typeofpur=document.getElementById("purpose")
    let terms=document.getElementById("terms")

    if(Fname.value === ""){ message.innerHTML="please fill the First-Name section";return;}
    if(Lname.value===""){message.innerHTML="please fill the Last-Name section";return}
    if(birthdate.value===""){message.innerHTML="please fill the Date-Of-Birth section";return; 
    }else if(validdate()==0){message.innerHTML="Invalid Date ";return;}
    if(nationality.value===""){message.innerHTML="please fill the Nationality section";return;}
    if(pass.value===""){message.innerHTML="please fill the password section";return;}
    else if(pass.value.length<8){message.innerHTML="please enter a password contain 8 characters or more";return;}
    if(copass.value!=pass.value){message.innerHTML="the passwords does not match \n please make sure they are the same";return;}  
    if(phone.value===""){message.innerHTML="please fill the Phone section";return;}
    else if(phone.value.length<8){message.innerHTML="please enter a valid phone number";return;}
    if(Address.value===""){message.innerHTML="please fill the address section";return;}
    if(Incomesrc.value===""){message.innerHTML="please fill the Income-Source section";return;}
    if(typeofacc.value===""){message.innerHTML="please fill the type-of-account section";return;}
    // if(typeofpur.value===""){message.innerHTML="please fill the purpose section";return;}
    if(terms.checked===false){message.innerHTML="please accept our terms";return;}

    
    // localStorage.setItem("First-Name",Fname.value);
    // localStorage.setItem("Last-Name",Lname.value);
    // localStorage.setItem("birthdate",birthdate.value);
    // localStorage.setItem("nationality",nationality.value);
    // localStorage.setItem("email",email.value);
    // localStorage.setItem("phone",phone.value);
    // localStorage.setItem("password",pass.value);
    // localStorage.setItem("User-Id",generateID(1000,9999));
    // localStorage.setItem("Address",Address.value);
    // localStorage.setItem("Incomesrc",Incomesrc.value);
    // localStorage.setItem("Type-Of-Acc",typeofacc.value);
    // localStorage.setItem("Purpose",typeofpur.value);
    // localStorage.setItem("Balance",100.00);
   
    
    

    // // let spent_year=0.00;let spent_month=0.00;let spent_week=0.00;
    // let Spent_array=[0,0,0];
    
    // // let numTran_year=0.00;let numTran_month=0.00;let numTran_week=0.00;
    // let NumTran_array=[0,0,0]

    // // let received_year=100.00;let received_month=100.00;let received_week=100.00;
    // let received_array=[100.00,100.00,100.00]

    // localStorage.setItem("spent",JSON.stringify(Spent_array))
    // localStorage.setItem("received",JSON.stringify(received_array))
    // localStorage.setItem("TranNum",JSON.stringify(NumTran_array))



    message.innerHTML=""
    Next.style.display="none"
    signedup.style.display="block"

}

