<?php 
$bezeichnung_tisch = "Schreibtisch";
$bezeichnung_stuhl = "Bürostuhl";
$bezeichnung_lampe = "Lampe";
$bezeichnung_pctisch = "Computertisch";

$preis_tisch = 1999.00;
$preis_stuhl = 589.00;
$preis_lampe = 29.00;
$preis_pctisch = 999.00;

$netto_preis = $preis_lampe + $preis_tisch + $preis_pctisch + $preis_stuhl;
echo $netto_preis;

const MSTW = 19;

const EURO = "Euro";

$brutto_preis = $netto_preis / 100 * 119;

echo "<br>";
echo $brutto_preis;

$preis_pctisch = $preis_pctisch / 100 * 119;
$preis_stuhl = $preis_stuhl / 100 * 119;
$preis_tisch = $preis_tisch / 100 * 119;
$preis_lampe = $preis_lampe / 100 * 119;

echo "<br>";
echo "<p>$bezeichnung_pctisch preis: $preis_pctisch </p>";
echo "<br>";
echo "<p>$bezeichnung_lampe preis: $preis_lampe </p>";
echo "<br>";
echo "<p>$bezeichnung_tisch preis: $preis_tisch </p>";
echo "<br>";
echo "<p>$bezeichnung_stuhl preis: $preis_stuhl </p>";

$test = ["HH" => "Hamburg", "B" => "Berlin", "S" => "Stuttgart"];

echo $test['HH'];
echo "<br>";
echo $test['S'];
echo "<br>";
echo $test['B'];


