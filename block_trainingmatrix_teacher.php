<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * block_trainingmatrix_teacher file.
 *
 * @package    block_trainingmatrix_teacher
 * @copyright  2026 LMS-Labs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/trainingmatrix/lib.php');

class block_trainingmatrix_teacher extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_trainingmatrix_teacher');
    }

    public function get_content() {
        global $USER, $DB, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $context = context_system::instance();
        if (!has_capability('local/trainingmatrix:viewall', $context)) {
            $this->content->text = '<p style="color: hsl(217 8% 45%); font-size: 0.875rem;">You do not have permission to view staff compliance.</p>';
            return $this->content;
        }

        $this->content->text = $this->render_dashboard();
        return $this->content;
    }

    private function render_dashboard() {
        global $DB;
        
        $stats = local_trainingmatrix_get_compliance_stats();
        
        $complianceRate = $stats['total_staff'] > 0 
            ? round(($stats['fully_compliant'] / $stats['total_staff']) * 100) 
            : 0;
        
        $now = time();
        $thirtyDays = $now + (30 * 24 * 60 * 60);
        
        $attentionNeeded = $DB->get_records_sql("
            SELECT DISTINCT u.id, u.firstname, u.lastname, u.email,
                   (SELECT COUNT(*) FROM {local_tm_completions} tc 
                    WHERE tc.userid = u.id AND tc.expirydate < ?) as expired_count,
                   (SELECT COUNT(*) FROM {local_tm_completions} tc 
                    WHERE tc.userid = u.id AND tc.expirydate BETWEEN ? AND ?) as expiring_count
            FROM {user} u
            JOIN {local_tm_staff} s ON s.userid = u.id
            JOIN {local_tm_completions} tc ON tc.userid = u.id
            WHERE s.status = 'active' 
              AND (tc.expirydate < ? OR tc.expirydate BETWEEN ? AND ?)
            ORDER BY expired_count DESC, expiring_count DESC
            LIMIT 5
        ", [$now, $now, $thirtyDays, $now, $now, $thirtyDays]);
        
        $viewUrl = new moodle_url('/local/trainingmatrix/index.php');
        $reportsUrl = new moodle_url('/local/trainingmatrix/index.php', ['tab' => 'reports']);
        
        $attentionHtml = '';
        foreach ($attentionNeeded as $staff) {
            $badges = '';
            if ($staff->expired_count > 0) {
                $badges .= '<span class="tm-mini-badge danger">' . $staff->expired_count . ' expired</span>';
            }
            if ($staff->expiring_count > 0) {
                $badges .= '<span class="tm-mini-badge warning">' . $staff->expiring_count . ' expiring</span>';
            }
            
            $attentionHtml .= '
            <div class="tm-staff-row">
                <div class="tm-staff-info">
                    <div class="tm-staff-name">' . s($staff->firstname . ' ' . $staff->lastname) . '</div>
                    <div class="tm-staff-email">' . s($staff->email) . '</div>
                </div>
                <div class="tm-staff-badges">' . $badges . '</div>
            </div>';
        }

        $html = '
        <style>
            .tm-teacher-block {
                font-family: "Inter", system-ui, -apple-system, sans-serif;
                -webkit-font-smoothing: antialiased;
            }
            .tm-overview-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            .tm-overview-card {
                padding: 0.75rem;
                background: hsl(0 0% 98%);
                border-radius: 0.375rem;
                text-align: center;
                border-left: 3px solid;
                border-color: hsl(0 0% 89%);
            }
            .tm-overview-card.primary { border-color: hsl(217 91% 60%); }
            .tm-overview-card.success { border-color: hsl(142 76% 36%); }
            .tm-overview-card.warning { border-color: hsl(38 92% 50%); }
            .tm-overview-card.danger { border-color: hsl(0 84% 60%); }
            .tm-overview-value {
                font-size: 1.375rem;
                font-weight: 700;
                line-height: 1;
                color: hsl(0 0% 9%);
            }
            .tm-overview-card.success .tm-overview-value { color: hsl(142 76% 36%); }
            .tm-overview-card.warning .tm-overview-value { color: hsl(38 92% 40%); }
            .tm-overview-card.danger .tm-overview-value { color: hsl(0 84% 50%); }
            .tm-overview-label {
                font-size: 0.625rem;
                color: hsl(217 8% 45%);
                margin-top: 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }
            .tm-section-title {
                font-size: 0.8125rem;
                font-weight: 600;
                color: hsl(0 0% 20%);
                margin-bottom: 0.625rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .tm-section-title i {
                color: hsl(0 84% 50%);
            }
            .tm-staff-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.625rem;
                background: hsl(0 0% 98%);
                border-radius: 0.375rem;
                margin-bottom: 0.5rem;
                border: 1px solid hsl(0 0% 93%);
            }
            .tm-staff-name {
                font-size: 0.8125rem;
                font-weight: 500;
                color: hsl(0 0% 9%);
            }
            .tm-staff-email {
                font-size: 0.6875rem;
                color: hsl(217 8% 45%);
            }
            .tm-staff-badges {
                display: flex;
                gap: 0.25rem;
            }
            .tm-mini-badge {
                padding: 0.1875rem 0.5rem;
                border-radius: 0.375rem;
                font-size: 0.625rem;
                font-weight: 600;
            }
            .tm-mini-badge.danger { background: hsl(0 84% 95%); color: hsl(0 84% 45%); }
            .tm-mini-badge.warning { background: hsl(38 92% 95%); color: hsl(38 92% 35%); }
            .tm-action-btns {
                display: flex;
                gap: 0.5rem;
                margin-top: 1rem;
            }
            .tm-action-btn {
                flex: 1;
                display: block;
                text-align: center;
                padding: 0.625rem;
                border-radius: 0.375rem;
                font-size: 0.75rem;
                font-weight: 500;
                text-decoration: none;
                transition: background 150ms ease;
            }
            .tm-action-btn.primary {
                background: hsl(217 91% 60%);
                color: hsl(0 0% 98%) !important;
            }
            .tm-action-btn.primary:hover {
                background: hsl(217 91% 55%);
            }
            .tm-action-btn.secondary {
                background: hsl(0 0% 96%);
                color: hsl(0 0% 20%) !important;
                border: 1px solid hsl(0 0% 89%);
            }
            .tm-action-btn.secondary:hover {
                background: hsl(0 0% 93%);
            }
            .tm-no-attention {
                text-align: center;
                padding: 1.25rem;
                color: hsl(142 76% 36%);
            }
            .tm-no-attention i {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
        </style>
        <div class="tm-teacher-block">
            <div class="tm-overview-grid">
                <div class="tm-overview-card primary">
                    <div class="tm-overview-value">' . $stats['total_staff'] . '</div>
                    <div class="tm-overview-label">Total Staff</div>
                </div>
                <div class="tm-overview-card success">
                    <div class="tm-overview-value">' . $stats['fully_compliant'] . '</div>
                    <div class="tm-overview-label">Compliant</div>
                </div>
                <div class="tm-overview-card warning">
                    <div class="tm-overview-value">' . $stats['expiring_soon'] . '</div>
                    <div class="tm-overview-label">Expiring</div>
                </div>
                <div class="tm-overview-card danger">
                    <div class="tm-overview-value">' . $stats['expired'] . '</div>
                    <div class="tm-overview-label">Expired</div>
                </div>
            </div>';
            
        if (!empty($attentionNeeded)) {
            $html .= '
            <div class="tm-section-title">
                <i class="fa fa-exclamation-triangle"></i>
                Staff Needing Attention
            </div>
            ' . $attentionHtml;
        } else {
            $html .= '
            <div class="tm-no-attention">
                <i class="fa fa-check-circle"></i>
                <div>All staff are compliant!</div>
            </div>';
        }
        
        $html .= '
            <div class="tm-action-btns">
                <a href="' . $viewUrl->out() . '" class="tm-action-btn primary">
                    <i class="fa fa-th mr-1"></i> Dashboard
                </a>
                <a href="' . $reportsUrl->out() . '" class="tm-action-btn secondary">
                    <i class="fa fa-chart-bar mr-1"></i> Reports
                </a>
            </div>
        </div>';
        
        return $html;
    }

    public function applicable_formats() {
        return [
            'all' => false,
            'site' => true,
            'site-index' => true,
            'my' => true,
            'course-view' => false,
        ];
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return false;
    }
}
