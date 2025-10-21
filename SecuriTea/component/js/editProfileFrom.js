function openEditProfileModel(){
    editProfileForm.style.display = "flex";
    const name = document.getElementById("profile-name").textContent;
    const birthday = document.getElementById("profile-birthday").value;
    const gender = document.getElementById("profile-gender").value;
    const phone = document.getElementById("profile-phone").value;
    const email = document.getElementById("profile-email").value;
    const fromFirstName = document.getElementsByName("firstname");
    console.log(name);
    fromFirstName.value=name;
}




editProfileBtn.onclick = () => openEditProfileModel();