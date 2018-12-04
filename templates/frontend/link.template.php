<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 01.02.2018
 * Time: 15:28
 */
 ?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $page_options["TITLE"]; ?> | 50 Jahre Gymnasium Oesede</title>
    <meta name="description" content="50 Jahre Gymnasium Oesede, ein Grund zum Feiern, mehr dazu auf unserer Infoseite">
    <link rel="stylesheet" href="/css/styles.css">
    <script src="/js/jquery.js"></script>
    <script src="/js/gymoesede.js"></script>
</head>

<body>



<div class="menu">

    <div class="menu-branding">
        <img src="/img/gymlogo.svg" alt="Gymnasium Oesede Logo" height="40px">
    </div>

    <div class="hamburger-button">
        <span class="hamburger-btn" id="ham-btn-1"></span>
        <span class="hamburger-btn" id="ham-btn-2"></span>
        <span class="hamburger-btn" id="ham-btn-3"></span>
    </div>

</div>

<div class="sidemenu">

    <div class="ul-container-sidemenu">
        <ul class="ul-sidemenu">
            <li class="menu-link"><a href="/start">Startseite</a></li>
            <li class="menu-link"><a href="/lehrer">Lehrer</a></li>
            <li class="menu-link"><a href="/schueler">Schüler</a></li>
            <li class="menu-link"><a href="/klassen">Klassen</a></li>
            <li class="menu-link"><a href="/ags">Arbeitsgemeinschaften</a></li>
            <li class="menu-link"><a href="/special">Jubiläum</a></li>
        </ul>
    </div>

    <div class="closebutton">
        <span class="closebtn" id="closebtn1"></span>
        <span class="closebtn" id="closebtn2"></span>
    </div>

</div>

<div class="site-eyecatcher">
    <h1><?php echo $page_options["TITLE"];?></h1>
    <div class="site-eyecatcher-overlay"></div>
</div>

<div class="textcontainer" id="textcontainer-startseite">

    <h1><?php echo $page_options["TITLE"];?></h1>


    <div class="list">

        <?php

            // TODO: Pages der Kategorie in's Array $pages() fetchen (in PageBuilder) und hier anzeigen.

            echo '<h1>Still in Developement!</h1>';

        ?>



    </div>




</div>

<div class="footer">

    <div class="footer-content">
        <div class="footer-grid" id="grid1">
            <span class="grid-heading">50 Jahre - Gymnasium Oesede</span>
            <ul class="footer-links">
                <li><a href="/startseite">Startseite</a></li>
                <li><a href="/schueler">Schüler</a></li>
                <li><a href="/lehrer">Lehrer</a></li>
                <li><a href="/klassen">Klassen</a></li>
                <li><a href="/ags">Arbeitsgemeinschaften</a></li>
                <li><a href="/special">Jubiläum</a></li>
            </ul>
        </div>

        <div class="footer-grid" id="grid1">
            <span class="grid-heading">Infomation</span>
            <p>Diese Sonderseite existiert aufgrund des 50 Jährigen bestehen unserer Schule, die Inhalte wurden / werden von Schülern und Lehrern zusammengetragen.</p>
        </div>
    </div>

    <div class="footer-menu">
        <span>2018 - Gymnasium Oesede <a href="https://gymnasium-oesede.de/impressum">Impressum & Datenschutz</a></span>
    </div>

</div>

</body>

</html>
