<?php 
    session_start();
    include_once('php/db_connect.php');
    include_once('php/session_expiry.php');
    include_once("php/check_authorize.php");

    checkExpiredSession("REDIRECT");
    checkAuthorizeAccess("MANAGER");

    //Check Log in
    $is_signed_in = isset($_SESSION) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    if (!$is_signed_in) return;


    $username = $_SESSION['user']['username'];
    $user_id = $_SESSION['user']['user_id'];


    $sql = 
        'SELECT APPLICATION.application_id, APPLICATION.date_submitted, APPLICATION.approval_status, STAFF.username 
        FROM APPLICATION 
        INNER JOIN STAFF ON APPLICATION.applicant_ID=STAFF.user_id';

    $result = $conn->query($sql);

    if ($result->num_rows === 0) $_GET['message_success'] = 'Database search successfully. No application found';
    
    
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manager Dashboard</title>

    <?php include_once('php/components/head.php'); ?>
    
    <link rel="stylesheet" href="css/jumbotron.css">
    <link rel="stylesheet" href="css/managerHome.css">
    <link rel="stylesheet" href="css/dateclock.css">

    <script src="javascript/managerHome.js"></script>
    <script src="javascript/timeclock.js"></script>
</head>


<body>

    <?php include_once("php/components/nav.php"); ?>
    
    
    <div class="intro">


        <h3 class="brand-title">
            <i class="lab la-envira"></i>
            EzLeave
        </h3>

       
        <p class="brand-desc">
            Apply leaves with ease
        </p>

        <div class="date">
            <span class="date-day" id="date-day">28</span>
            <span class="date-mon" id="date-mon">January</span>
            <span class="date-dow" id="date-dow">(Monday)</span>
        </div>
    
        <div class="clock">
            <span class="clock-hr" id="clock-hr">88</span>
            <span class="clock-colon">:</span>
            <span class="clock-min" id="clock-min">88</span>
            <span class="clock-colon">:</span>
            <span class="clock-sec" id="clock-sec">88</span>
            <span class="clock-ampm" id="clock-ampm">AM</span>
        </div>
    </div>

    

    <main class="container">
    
        <div class="container_nav">
            <h2><i class="las la-file-invoice"></i> Leave Applications</h2> 
            

            <div class="dropdown">
                <button class="button dropbtn"><i class="las la-sort"></i></button>
                <div class="dropdown-content">
                    <button class="button"><i class="las la-calendar-plus"></i> Date added</button>
                    <button class="button"><i class="las la-calendar-day"></i> Leave date</button>
                </div>
            </div>

            <div class="dropdown">
                <button class="button dropbtn"><i class="las la-filter"></i></button>
                <div class="dropdown-content">
                        <input type="checkbox" id="check1" class="checkbox">
                        <label for="check1"> Pending<i class="las la-spinner" style="color: #1e80c1;padding-left: 1em;"></i></label>
                    
                        <input type="checkbox" id="check2" class="checkbox">
                        <label for="check2"> Verified<i class="las la-check" style="color:rgb(8,181,8);padding-left: 1em;"></i></label>

                        <input type="checkbox" id="check3" class="checkbox">
                        <label for="check3"> Rejected<i class="las la-times" style="color:rgb(233,45,45);padding-left: 1em;"></i></label>

                        <input type="checkbox" id="check4" class="checkbox">
                        <label for="check4"> Expired<i class="las la-times-circle" style="color:grey;padding-left: 1em;"></i></label>
                </div>
            </div>

        </div>
        
        <hr class="line">

        <?php include_once('php/components/messagebox.php'); ?>

        <div class="container_item">


            <?php include_once("php/components/application_card.php"); ?>



        </div>
    </main>

</body>
</html>