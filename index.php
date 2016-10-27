<?php ?>
<!DOCTYPE html>
<html>
<head>
<title>NCBS Calendar</title>

<meta name="author" content="Serhioromano">
<mata name="author" content="Dilawar Singh"
<meta charset="UTF-8">

<link rel="stylesheet" href="components/bootstrap2/css/bootstrap.css">
<link rel="stylesheet" href="components/bootstrap2/css/bootstrap-responsive.css">
<link rel="stylesheet" href="css/calendar.css">


</head>
<body>
<div class="container">
    <div class="hero-unit">
        <h1>NCBS Schedular and Event Calendar</h1>
    </div>

    <div class="page-header">

        <div class="pull-right form-inline">
            <div class="btn-group">
                <button class="btn btn-primary" data-calendar-nav="prev"><< Prev</button>
                <button class="btn" data-calendar-nav="today">Today</button>
                <button class="btn btn-primary" data-calendar-nav="next">Next >></button>
            </div>
            <div class="btn-group">
                <button class="btn btn-warning" data-calendar-view="year">Year</button>
                <button class="btn btn-warning active" data-calendar-view="month">Month</button>
                <button class="btn btn-warning" data-calendar-view="week">Week</button>
                <button class="btn btn-warning" data-calendar-view="day">Day</button>
            </div>
        </div>

		<h3><selecteddate></selecteddate></h3>
	</div>


    <div class="row">
        <div class="span9">
            <div id="calendar"></div>
        </div>
        <div class="span3">
            <div class="row-fluid">
                <label class="checkbox">
                    <input type="checkbox" value="#events-modal" id="events-in-modal"> Open events in modal window
                </label>
                <label class="checkbox">
                    <input type="checkbox" id="format-12-hours"> 12 Hour format
                </label>
                <label class="checkbox">
                    <input type="checkbox" id="show_wb" checked> Show week box
                </label>
                <label class="checkbox">
                    <input type="checkbox" id="show_wbn" checked> Show week box number
                </label>
            </div>

            <h4>Events</h4>
            <small>This list is populated with events dynamically</small>
            <ul id="eventlist" class="nav nav-list"></ul>

        </div>
    </div>

    <div class="clearfix"></div>
    <br><br>
    <a href="https://github.com/dilawar/bootstrap-calendar/issues" class="btn btn-block btn-info">
        <center>
            <span class="lead">
                Submit an issue, ask questions or give your ideas here!<br>
            </span>
            <small>Please do not post your "How to ..." questions in comments. use GitHub issue tracker.</small>
        </center>
    </a>
    <br><br>

    <script type="text/javascript" src="components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="components/underscore/underscore-min.js"></script>
    <script type="text/javascript" src="components/bootstrap2/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="components/jstimezonedetect/jstz.min.js"></script>
    <script type="text/javascript" src="js/language/bg-BG.js"></script>
    <script type="text/javascript" src="js/language/nl-NL.js"></script>
    <script type="text/javascript" src="js/language/fr-FR.js"></script>
    <script type="text/javascript" src="js/language/de-DE.js"></script>
    <script type="text/javascript" src="js/language/el-GR.js"></script>
    <script type="text/javascript" src="js/language/it-IT.js"></script>
    <script type="text/javascript" src="js/language/hu-HU.js"></script>
    <script type="text/javascript" src="js/language/pl-PL.js"></script>
    <script type="text/javascript" src="js/language/pt-BR.js"></script>
    <script type="text/javascript" src="js/language/ro-RO.js"></script>
    <script type="text/javascript" src="js/language/es-CO.js"></script>
    <script type="text/javascript" src="js/language/es-MX.js"></script>
    <script type="text/javascript" src="js/language/es-ES.js"></script>
    <script type="text/javascript" src="js/language/es-CL.js"></script>
    <script type="text/javascript" src="js/language/ru-RU.js"></script>
    <script type="text/javascript" src="js/language/sk-SR.js"></script>
    <script type="text/javascript" src="js/language/sv-SE.js"></script>
    <script type="text/javascript" src="js/language/zh-CN.js"></script>
    <script type="text/javascript" src="js/language/cs-CZ.js"></script>
    <script type="text/javascript" src="js/language/ko-KR.js"></script>
    <script type="text/javascript" src="js/language/zh-TW.js"></script>
    <script type="text/javascript" src="js/language/id-ID.js"></script>
    <script type="text/javascript" src="js/language/th-TH.js"></script>
    <script type="text/javascript" src="js/calendar.js"></script>
    <script type="text/javascript" src="js/app.js"></script>

</div>
</body>
</html>
