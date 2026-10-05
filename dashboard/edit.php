<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Edit mode</title>
  <link rel="stylesheet" href="../grades.css">
  <link rel="stylesheet" href="../hover.css">
  <link rel="stylesheet" href="../colours.css">
  <script src="../backend/dashfetch.js" defer></script>
  <script src="../backend/dashcheck.js" defer></script>
  <script src="../backend/check.js" defer></script>
  <script src="../backend/info.js" defer></script>
  <script src="../backend/logout.js" defer></script>
  <script src="../js/fetchannouncements.js" defer></script>
  <style>
    .navbar ul.admin,
    .navbar ul.teacher{
      background-image: radial-gradient(circle, #ffa600,#aa6f00) !important;
    }
  </style>
  <!-- Include jsPDF and html2canvas libraries -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <link rel="icon" href="../school.png">
</head>

<body class="<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['strand']) ? $_SESSION['strand'] : ''); ?>">
  <div class="errir"><p>Screen Size is too small</p></div>
  <div class="navbar">
  <ul class="<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['strand']) ? $_SESSION['strand'] : ''); ?>">
      <div class="home">
      <li><a href="../about.html"><img src="../school.png"></a></li>
      <li class="title"><a>Dashboard - Edit mode</a></li>
      </div>
      <div class="link" style="float: right;">
        <div class="dropdown">
          <button class="dropbut">≡</button>
          <div class="dropdown-content">
            <a href="../index.html">Home</a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="#" id="logoutdrop">Log out</a>

          </div>
        </div>
        <li><a style="float: right; font-size: large; color: white;" href="../index.html" >Home</a></li>
        <li><a style="float: right; font-size: large; color: white;" class="active" href="../dashboard.php" >Dashboard</a></li>
        <li><a style="float: right; font-size: large; color: white;" href="#" id="logoutLink" >Log out</a></li>
      </div>
    </ul>
  </div>
  <div class="body">
    <div class="left">
      <div class="box">
        <ul>
          <ul>
            <li><a id="full-name"> </a></li>
            <li><a id="lrn"> </a></li>
            <li><a id="full-section"> </a></li>
            <hr style="color: #000000; background-color: #000000; height: 1px; width:100%;">
            <li><a id="announcements"></a></li>
            <a href="../edit_announcements.php" style="margin-top: 20px;" id="adminOnly"><button>Edit Announcements</button></a>
          </ul> 
        </ul>
      </div>
    </div>
    <div class="right">
      <div class="box">
        <div class="selectarea">
          <select id="grade" onchange="fetchGrades()">
              <option value="11">Grade 11</option>
            <option value="12" selected>Grade 12</option>
          </select>
          <select id="semester" onchange="fetchGrades()">
            <option value="1">Semester 1 (Q1 & Q2)</option>
            <option value="2" selected>Semester 2 (Q3 & Q4)</option>
          </select>
          <select id="quarter" onchange="fetchGrades()">
            <option value="sectionSelect">Quarter</option>
          </select>
          <select id="sectionSelect" onchange="fetchGrades()">
            <option value="sectionSelect">Section</option>
          </select>
          <select id="schoolYear" onchange="fetchGrades()">
            <option value="schoolYear">School Year</option>
          </select>
          <button id="saveGradesBtn">Save Changes</button>
        </div>
        <a style="font-size: medium; margin-bottom:10px;">Double click to edit</a>
    <!-- Grade Table -->
        <div>
          <table id="gradesTable">
            <thead>
              <tr><th colspan="17">Loading...</th></tr>
            </thead>
            <tbody>
              <tr><td colspan="17">Select filters to load data</td></tr>
            </tbody>
          </table>
        </div>
        <button onclick="exportTableToPDF()" style="margin-top: 10px;">Export Table to PDF</button>
        <a href="../add-students.php" style="margin-top: 10px;"><button>Add Students</button></a>
        <a href="../add_teacher.html" style="margin-top: 10px;" id="adminOnly"><button>Add Teachers</button></a>
      </div>
    </div>
  </div>

</body>

</html>
<!--olicierrv, quipp3r-->
