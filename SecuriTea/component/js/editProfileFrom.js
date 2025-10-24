function openEditProfileModel() {
    editProfileForm.style.display = "flex";
    const name = document.getElementById("profile-name").textContent;
    const birthday = document.getElementById("profile-birthday").textContent;
    const gender = document.getElementById("profile-gender").textContent;
    const phone = document.getElementById("profile-phone").textContent;
    const email = document.getElementById("profile-email").textContent;
    const fromFirstName = document.getElementsByName("firstname");
    const fromLastName = document.getElementsByName("lastname");
    const fromBirthdate = document.getElementsByName("birthdate");
    const fromGender = document.getElementsByName("gender");
    const fromPhone = document.getElementsByName("phone");
    const fromEmail = document.getElementsByName("email");
    console.log(name, birthday, gender, phone, email);
    fromFirstName[0].value = name.split(" ")[0];
    fromLastName[0].value = name.split(" ")[1];
    fromBirthdate[0].value = formatDateForInput(birthday);
    fromGender[0].value = gender;
    fromPhone[0].value = phone;
    fromEmail[0].value = email;
}

function closeEditProfileModel() {
    editProfileForm.style.display = "none";
}

function saveEditProfile() {
    const fromFirstName = document.getElementsByName("firstname");
    const fromLastName = document.getElementsByName("lastname");
    const fromDate = document.getElementsByName("date");
    const fromGender = document.getElementsByName("gender");
    const fromPhone = document.getElementsByName("phone");
    const fromEmail = document.getElementsByName("email");
    document.getElementById("profile-name").textContent = `${fromFirstName[0].value} ${fromLastName[0].value}`;
    document.getElementById("profile-birthday").textContent = fromDate[0].value.replace(/^(\d+)\/(\d+)\/(\d+)$/, "$1年$2月$3日");
    document.getElementById("profile-gender").textContent = fromGender[0].value;
    document.getElementById("profile-phone").textContent = fromPhone[0].value;
    document.getElementById("profile-email").textContent = fromEmail[0].value;
    closeEditProfileModel();
}

function formatDateForInput(dateStr) {
    const [year, month, day] = dateStr.match(/(\d+)年(\d+)月(\d+)日/).slice(1);
    const mm = month.padStart(2, "0");
    const dd = day.padStart(2, "0");
    return `${year}-${mm}-${dd}`;
}

editProfileBtn.addEventListener("click", openEditProfileModel);
cancelBtn.onclick = () => closeEditProfileModel();
saveBtn.onclick = () => closeEditProfileModel();