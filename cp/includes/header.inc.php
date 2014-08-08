<?php @session_start(); if(@$_GET['theme']!='') {$_SESSION['t'] = (int)$_GET['theme'];} else { $_SESSION['t'] = 1; } $CONFIG['theme'] = (int)@$_SESSION['t'];?>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/theme<?php echo $CONFIG['theme']; ?>.css">
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/bootstrap-editable.css" rel="stylesheet"/>
<script src="js/bootstrap-editable.min.js"></script>		
<script src="js/jquery.form.js"></script>
<style> html body { font-family: 'Ubuntu', 'sans-serif'; } .form-control.input-sm { width: 120px; } .navbar { background-color: #004918 !important; } .navbar { min-height: 105px !important; background-image: none !important; } .btn-danger { background-color: #e83c28 !important; background-image:  none !important; } .btn-success { background-color: #004918 !important; background-image:  none !important; } </style>