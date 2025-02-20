<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="stylesheet" href="colours.css">
  <link rel="stylesheet" href="grades.css">
  <link rel="stylesheet" href="hover.css">
  <script src="dashcheck.js" defer></script>
  <script src="check.js" defer></script>
  <script src="info.js" defer></script>
  <script src="logout.js" defer></script>
  <link rel="icon" href="school.png">
</head>

<body>
  <div class="errir"><p>Screen Size is too small</p></div>
  <div class="navbar">
  <ul class="<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['strand']) ? $_SESSION['strand'] : ''); ?>">
  <div class="home">
        <li><a style="font-size: large; color: white;" href="index.html">Home</a></li>
      </div>
      <div>
            <li class="title"><a>Dashboard</a></li>
      </div>
      <div class="link" style="float: right;">
        <div class="dropdown">
          <button class="dropbut">≡</button>
          <div class="dropdown-content">
            <a href="index.html">Home</a>
            <a>Activities</a>
            <a>Schedule</a>
            <a href="DO_s2024_009.pdf">School Calendar</a>
            <a href="#" id="logoutdrop">Log out</a>
          </div>
        </div>
        <li><a style="float: right; font-size: large; color: white;" href="#" id="logoutLink" >Log out</a></li>
      </div>
    </ul>
  </div>
  <div class="body">
    <div class="left">
      <div class="box">
        <ul>
            <li><a id="full-name"> </a></li>
            <li><a id="lrn"> </a></li>
            <li><a id="full-section"> </a></li>
        <hr style="color: #000000; background-color: #000000; height: 1px; width:100%;">
        </ul>
      </div>
    </div>
    <div class="right">
      <div class="box">
        <div>
          <h3 style="text-align: center;">First Semester</h3>
          <table>
            <tr>
              <th>Subject</th>
              <th>Quarter 1</th>
              <th>Quarter 2</th>
            </tr>
            <tr>
              <td>Computer Programming III</td>
              <td>94</td>
              <td>98</td>
            </tr>
            <tr>
              <td>Practical Research II</td>
              <td>89</td>
              <td>91</td>
            </tr>
            <tr>
              <td>Filipino sa Piling Larang</td>
              <td>92</td>
              <td>92</td>
            </tr>
            <tr>
              <td>Introduction to the Philosophy of the Human Person</td>
              <td>90</td>
              <td>91</td>
            </tr>
            <tr>
              <td>21st Century Literature from the Philippines and the World</td>
              <td>96</td>
              <td>98</td>
            </tr>
            <tr>
              <td>Physical Education and Health</td>
              <td>91</td>
              <td>98</td>
            </tr>
          </table>
          <h3 style="text-align: center;">Second Semester</h3>
          <table>
            <tr>
              <th>Subject</th>
              <th>Quarter 3</th>
              <th>Quarter 4</th>
            </tr>
            <tr>
              <td>Computer Programming IV</td>
              <td>94</td>
              <td>98</td>
            </tr>
            <tr>
              <td>Investigations, Inquiries, and Immersion</td>
              <td>89</td>
              <td>91</td>
            </tr>
            <tr>
              <td>English for Academic Purposes</td>
              <td>92</td>
              <td>92</td>
            </tr>
            <tr>
              <td>Personal Development</td>
              <td>90</td>
              <td>91</td>
            </tr>
            <tr>
              <td>Work Immersion</td>
              <td>96</td>
              <td>98</td>
            </tr>
            <tr>
              <td>Contemporary Philippines Arts from the Regions</td>
              <td>96</td>
              <td>98</td>
            </tr>
            <tr>
              <td>Physical Education and Health</td>
              <td>91</td>
              <td>98</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

</body>

</html>
<!--olicierrv, quipp3r-->
