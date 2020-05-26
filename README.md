<h1>Events App</h1>
<h3>Version 1.0</h3>
This application is based on SLIM Micro Framework.
<br />
It allows to manage Events (Periodic meetups) with files' uploading system (example Abstracts and Documents word or pdf), setup accomodations for the night (more-than-one-day events) and related booking, sub-events (round-tables) with a given max number of participants and subscription's deadline. 
<br />
Download list of participants in Excel format and Zip archive with all files uploaded by the users for each Event.
<br />
Users' notification system.
<br />
Easily customisable authentication system. You can set your own users provider or use oAuth or whatever you want by altering app/Auth/Auth.php file, adding the method you prefer.
<br />
<br />
<br />
<b>Core</b> file \bootstrap\app.php.
<br />
<b>Routes</b> file \App\Routes.php
<br />
<b>Views</b> files \resources\views\
<br />
<b>Assets</b> files \public\assets\
<h3>Framework version</h3>
<b>Slim 3.0</b>

<h3>Libraries</h3>
<ul>
  <li>Twig</li>
  <li>Eloquent ORM</li>
  <li>Respect validation for form validations</li>
  <li>Slim Csrf</li>
  <li>PHPMailer</li>
  <li>form-manager: https://github.com/oscarotero/form-manager </li>
</ul>

