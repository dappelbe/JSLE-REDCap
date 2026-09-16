<?php
/**
 * -----------------------------------------------------------------------------
 * File: recalculate.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 *
 *
 * @package
 * @author    orms0734
 * @version   0.0.1
 * @created   15/05/2026 13:40
 * -----------------------------------------------------------------------------
 */

namespace UoL\UKJSLE;
use \REDCap as REDCap;
require_once __DIR__ . '/../vendor/autoload.php';
global $Proj;

if (!isset($project_id)) {
    die('Project ID is a required field @ Index');
}

$em = new UKJSLE();

?>
<h1>Hello - recalculating previous bilags</h1>

<?php
$em->recalculateBilags($_GET['pid']);
?>