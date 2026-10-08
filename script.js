function validateOTP() {

    let otp = document.getElementById("otp").value;

    if (!/^[0-9]{6}$/.test(otp)) {
        alert("Please enter a valid 6-digit OTP.");
        return false;
    }

    return true;
}