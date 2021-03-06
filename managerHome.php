<?php
    //? The sorting and filter is done in cookie: (MANAGERHOME_FILTER and MANAGERHOME_SORT)
    //? 
    //? The MANAGERHOME_FILTER can take the following values:
    //?     { ALL, PENDING, VERIFIED, REJECTED, EXPIRED }
    //?
    //? The MANAGERHOME_SORT can take the following values:
    //?     { DATEADDED_ASC, DATEADDED_DESC, LEAVEDATE_ASC, LEAVEDATE_DESC }
?>



<?php 
    session_start();

    include_once('php/db_connect.php');
    include_once('php/session_expiry.php');
    include_once("php/check_authorize.php");
    include_once("php/update_expired_application.php");

    checkExpiredSession("REDIRECT");
    checkAuthorizeAccess("MANAGER");
    updateExpiredApplication($conn);

    setDefaultCookie();
    checkSetCookie();

    $filter = isset($_COOKIE['MANAGERHOME_FILTER'])? $_COOKIE['MANAGERHOME_FILTER']: '';
    $sort = isset($_COOKIE['MANAGERHOME_SORT'])? $_COOKIE['MANAGERHOME_SORT']: '';


    //* Retrieve a list of applications, according to filter and sort order
    $wherestmt = $filter === 'ALL' || $filter === ''? '': "WHERE approval_status = '$filter'";
    
    if ($sort === 'DATEADDED_ASC') $sortstmt = 'ORDER BY date_submitted ASC';
    else if ($sort === 'LEAVEDATE_ASC') $sortstmt = 'ORDER BY leave_date ASC';
    else if ($sort === 'LEAVEDATE_DESC') $sortstmt = 'ORDER BY leave_date DESC';
    else $sortstmt = 'ORDER BY date_submitted DESC';


    $query = "
        SELECT 
            application.application_id, 
            application.date_submitted, 
            application.leave_date, 
            application.approval_status, 
            staff.username 
        FROM application 
        INNER JOIN staff ON application.applicant_id=staff.user_id
        $wherestmt
        $sortstmt
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt->execute()) die("Error 500 - Failed to query database");
    $result = $stmt->get_result();

    if ($result->num_rows === 0) $_GET['message_warning'] = 'No applications found.';
?>


<?php
    function setDefaultCookie() {
        if (!isset($_COOKIE['MANAGERHOME_FILTER'])) {
            setcookie('MANAGERHOME_FILTER', 'ALL');
            $_COOKIE['MANAGERHOME_FILTER'] = "ALL";
        }
        if (!isset($_COOKIE['MANAGERHOME_SORT'])) {
            setcookie('MANAGERHOME_SORT', 'DATEADDED_DESC');
            $_COOKIE['MANAGERHOME_SORT'] = "DATEADDED_DESC";
        }
    }

    function checkSetCookie() {
        if (isset($_GET['filter'])) {
            setcookie('MANAGERHOME_FILTER', $_GET['filter']);
            $_COOKIE['MANAGERHOME_FILTER'] = $_GET['filter'];
        }
        if (isset($_GET['sort'])) {
            setcookie('MANAGERHOME_SORT', $_GET['sort']);
            $_COOKIE['MANAGERHOME_SORT'] = $_GET['sort'];
        }
    }
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
                <button class="button dropbtn" title='Sort'><i class="las la-sort"></i></button>
                <div class="dropdown-content">
                    <a href='managerHome.php?sort=DATEADDED_ASC'>
                        <button class="button <?php echo $sort === 'DATEADDED_ASC'? 'selected': ''; ?>"><i class="las la-plus-square"></i> Date Added (Asc)</button>
                    </a>
                    <a href='managerHome.php?sort=DATEADDED_DESC'>
                        <button class="button <?php echo $sort === 'DATEADDED_DESC'? 'selected': ''; ?>"><i class="las la-plus-square"></i> Date Added (Desc)</button>
                    </a>
                    <a href='managerHome.php?sort=LEAVEDATE_ASC'>
                        <button class="button <?php echo $sort === 'LEAVEDATE_ASC'? 'selected': ''; ?>"><i class="las la-calendar-week"></i> Leave Date (Asc)</button>
                    </a>
                    <a href='managerHome.php?sort=LEAVEDATE_DESC'>
                        <button class="button <?php echo $sort === 'LEAVEDATE_DESC'? 'selected': ''; ?>"><i class="las la-calendar-week"></i> Leave Date (Desc)</button>
                    </a>
                </div>
            </div>

            <div class="dropdown">
                <button class="button dropbtn" title='Filter by Application Status'><i class="las la-filter"></i></button>
                <div class="dropdown-content">
                    <a href='managerHome.php?filter=ALL'>
                        <button class="button <?php echo $filter === 'ALL'? 'selected': ''; ?>"><i class="lab la-wpforms"></i> All</button>
                    </a>
                    <a href='managerHome.php?filter=PENDING'>
                        <button class="button <?php echo $filter === 'PENDING'? 'selected': ''; ?>"><i class="las la-spinner"></i> Pending</button>
                    </a>
                    <a href='managerHome.php?filter=APPROVED'>
                        <button class="button <?php echo $filter === 'APPROVED'? 'selected': ''; ?>"><i class="las la-check-circle"></i> Approved</button>
                    </a>
                    <a href='managerHome.php?filter=REJECTED'>
                        <button class="button <?php echo $filter === 'REJECTED'? 'selected': ''; ?>"><i class="las la-times-circle"></i> Rejected</button>
                    </a>
                    <a href='managerHome.php?filter=EXPIRED'>
                        <button class="button <?php echo $filter === 'EXPIRED'? 'selected': ''; ?>"><i class="las la-calendar-times"></i> Expired</button>
                    </a>
                </div>
            </div>

        </div>
        
        <hr class="line">

        <?php include_once('php/components/messagebox.php'); ?>

        <div class="container_item">
            <?php 
                while ($row = $result->fetch_assoc())
                    include('php/components/application_card.php');
            ?>
        </div>
    </main>

</body>
</html>