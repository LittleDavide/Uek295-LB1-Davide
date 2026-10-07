<?php
// Starting font size
$fontSize = 1;
 
// Number of iterations
$totalCount = 100;
 
// Loop from 1 to $totalCount
for ($i = 1; $i <= $totalCount; $i++) {
    // Output the number with increasing font size
    echo "<span style='font-size: {$fontSize}px;'>$i</span><br>";
 
    // Increase font size for next iteration
    $fontSize += 1; // Increase by 1px each time
}

echo "<h1>Gorillaz</h1>";
echo "<p>Geboren am 1999</p> <br>";
echo "<p>2D MURDOSJND </p><br>";
echo "<p>Lorem Ipsum dala </p> <br>";
echo "<p style='font-weight: bold;'>Lorem Ispum Dala</p>";

$a = 7; 
$b = "30 Euro"; 
$c = "!";

echo ('<strong>\'Text\'</strong>' . $a ." Text " . $b);