<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'aliyudntt10@gmail.com';                 // SMTP username
    $mail->Password   = 'majtrumfmcndqmjo';                      // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;             // Enable implicit TLS encryption
    $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    session_start();

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'p') {
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    // Import database
    include("../connection.php");
    $sqlmain = "select * from patient where pemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pid"];
    $username = $userfetch["pname"];

    if ($_POST) {
        if (isset($_POST["booknow"])) {
            $apponum = $_POST["apponum"];
            $scheduleid = $_POST["scheduleid"];
            $date = $_POST["date"];
            $scheduleid = $_POST["scheduleid"];
            $sql2 = "insert into appointment(pid,apponum,scheduleid,appodate) values ($userid,$apponum,$scheduleid,'$date')";
            $result = $database->query($sql2);
            header("location: appointment.php?action=booking-added&id=" . $apponum . "&titleget=none");

            // Send email confirmation
            try {
                // Recipients
                $mail->setFrom('aliyudntt10@gmail.com', 'YUMSUK Clinic');
                $mail->addAddress($useremail); // Send email to the patient

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Appointment Confirmation';
                $Body = "Dear " . $username . ",<br><br>Your appointment has been <b>booked</b> successfully.<br><br>Thank you for choosing YUMSUK Clinic.<br><br>Best regards,<br>YUMSUK Clinic";

                $mail->send();
                echo 'Email confirmation has been sent';
            } catch (Exception $e) {
                echo "Email confirmation could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
