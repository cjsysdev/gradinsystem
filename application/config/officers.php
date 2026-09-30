<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class officer positions — the whitelist every officer write is validated
 * against, and the display order everywhere officers are listed.
 *
 * Officers are recorded per section per semester in `section_officers`
 * (see application/models/Section_officer.php). The key is what is stored in
 * the database; the label is what admins and exports see. Renaming a label is
 * safe; renaming a KEY orphans existing rows, so add a new key instead.
 *
 * Loaded sectioned to keep these keys out of the global config namespace:
 *   $this->load->config('officers', TRUE);
 *   $this->config->item('officer_positions', 'officers');
 */
$config['officer_positions'] = [
    'president'  => 'President',
    'vice_pres'  => 'Vice President',
    'secretary'  => 'Secretary',
    'treasurer'  => 'Treasurer',
    'pio'        => 'P.I.O.',
    'peace'      => 'Peace Officer',
    'beadle'     => 'Beadle',
];
