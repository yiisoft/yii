<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" type="text/css" href="css/main.css" />
<title>Ελεγκτής Απαιτήσεων Yii</title>
</head>

<body>
<div id="page">

<div id="header">
<h1>Ελεγκτής Απαιτήσεων Yii</h1>
</div><!-- header-->

<div id="content">
<h2>Περιγραφή</h2>
<p>
Αυτή η σελίδα ελέγχει αν η παραμετροποίηση του διακομιστή σας είναι σωστή
για την εκτέλεση των εφαρμογών ιστού του <a href="http://www.yiiframework.com/">Yii</a>.
Ελέγχει αν ο διακομιστής εκτελεί τη σωστή έκδοση PHP, αν έχουν φορτωθεί
οι κατάλληλες επεκτάσεις PHP και αν οι ρυθμίσεις του αρχείου php.ini είναι σωστές.
</p>

<h2>Συμπέρασμα</h2>
<p>
<?php if($result>0): ?>
Συγχαρητήρια! Η παραμετροποίηση του διακομιστή σας ικανοποιεί όλες τις απαιτήσεις του Yii.
<?php elseif($result<0): ?>
Η παραμετροποίηση του διακομιστή σας ικανοποιεί τις ελάχιστες απαιτήσεις του Yii. Παρακαλούμε δείτε με προσοχή τις παρακάτω προειδοποιήσεις εφόσον η εφαρμογή σας θα χρησιμοποιεί τα αντίστοιχα χαρακτηριστικά.
<?php else: ?>
Δυστυχώς, η παραμετροποίηση του διακομιστή σας δεν ικανοποιεί τις απαιτήσεις του Yii.
<?php endif; ?>
</p>

<h2>Λεπτομέρειες</h2>

<table class="result">
<tr><th>Όνομα</th><th>Αποτέλεσμα</th><th>Απαιτούμενο από</th><th>Σημειώσεις</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Passed' : ($requirement[1] ? 'Failed' : 'Warning'); ?>
	</td>
	<td>
	<?php echo $requirement[3]; ?>
	</td>
	<td>
	<?php echo $requirement[4]; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
<td class="passed">&nbsp;</td><td>πέρασε</td>
<td class="failed">&nbsp;</td><td>απέτυχε</td>
<td class="warning">&nbsp;</td><td>προειδοποίηση</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
</body>
</html>