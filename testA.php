<?php
/**
 * Created by PhpStorm.
 * User: tamelo
 * Date: 2014/01/31
 * Time: 2:21 PM
 */

$file = "<p style=\"text-align: center;\"><br /><br /><span style=\"font-size: large;\"><strong>Personality-abc</strong></span><br /><br /><span style=\"font-size: medium;\"><strong><span style=\"color: #000080;\">Based on the scientific research of the Behaviourist Karen Horney</span></strong></span></p>
<p style=\"text-align: center;\"><br /><br /><strong><span style=\"color: #333399; font-size: small;\">powered by the</span></strong><br /><br /><span style=\"font-size: large; color: #333399;\"><strong>International Institute for Behaviour Analysis Ltd</strong></span></p>
<p style=\"text-align: center;\"><span style=\"font-size: large;\"><strong></strong></span><br /><br /><span style=\"font-size: medium; color: #000080;\">tel.&nbsp;&nbsp;&nbsp; +27 (0)72 057 0966</span><br /><br /><span style=\"font-size: medium; color: #000080;\">&nbsp;efax. +27 (0)86 541 5102</span></p>
<p style=\"text-align: center;\"><br /><br /><span style=\"font-size: medium; color: #3366ff;\">support@personality-abc.com</span><br /><br /><span style=\"font-size: medium; color: #3366ff;\">&nbsp;www.personality-abc.com</span><br /><br /><br /></p>";

echo htmlentities($file);
echo "<br />";
echo  htmlspecialchars_decode($file);
echo "<br />";
echo  html_entity_decode($file);
?>