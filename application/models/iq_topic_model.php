<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Shared, storage-free helper for the two topic-file widgets (iq_discussion,
// iq_micro): where a topic JSON lives, and how a saved answers blob grades
// against it.
//
// The graders were originally private to GroupWorkController (the only place
// that needed them, on submit). They moved here when the admin group-submission
// page needed the SAME translation for a live, unsubmitted draft — grading a
// blob is now done in exactly one place, so a scoring rule can't drift between
// "what the group's submit recorded" and "what the instructor sees while they
// work".
//
// Both graders take topic `sections` plus the answers half of the shared
// live-state blob, and return the same {score, total, results} shape that
// classworks.code stores — results being the list the iq_* widget views render.
class Iq_topic_model extends CI_Model
{
    // Topic JSON files live in assets/json/ or one class-code folder down.
    public function resolve_file($topic)
    {
        if (!preg_match('/^[a-z0-9_]+$/', (string) $topic)) {
            return false;
        }
        $base   = FCPATH . 'assets/json/';
        $direct = $base . $topic . '.json';
        if (file_exists($direct)) {
            return $direct;
        }
        foreach (glob($base . '*/' . $topic . '.json') ?: [] as $match) {
            return $match;
        }
        return false;
    }

    // Decoded topic data, or null when the topic is missing/malformed.
    public function load_topic($topic)
    {
        $file = $this->resolve_file($topic);
        if (!$file) {
            return null;
        }
        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['sections']) || !is_array($data['sections'])) {
            return null;
        }
        return $data;
    }

    // Grades an iq_micro answers blob. Mirrors the client-side
    // gradeAnswer()/recomputeStats() logic in _interactive_micro_template.php,
    // but never trusts the client's score. $answers is keyed
    // "{sectionIndex}:{chunkIndex}" for micro-checks and "{sectionIndex}:q" for
    // checkpoints, matching the flattened screen order the template walks
    // (objectives -> chunks+checkpoint per section -> recap are not graded and
    // carry no key here).
    public function grade_micro(array $sections, array $answers)
    {
        $score   = 0;
        $total   = 0;
        $results = [];

        $normalize = function ($str) {
            $str = strtolower(trim((string) $str));
            $str = preg_replace('/;+\s*$/', '', $str);
            $str = preg_replace('/\s+/', ' ', $str);
            return $str;
        };

        foreach ($sections as $si => $section) {
            foreach ($section['chunks'] ?? [] as $ci => $chunk) {
                $check = $chunk['check'] ?? null;
                if (!is_array($check) || empty($check['question']) || empty($check['options'])) {
                    continue;
                }
                $total++;

                $entry          = $answers[$si . ':' . $ci] ?? [];
                $sel            = isset($entry['sel']) ? (int) $entry['sel'] : -1;
                $correct_idx    = (int) ($check['correct'] ?? -1);
                $chosen         = ($sel >= 0 && isset($check['options'][$sel])) ? $check['options'][$sel] : '';
                $correct_answer = isset($check['options'][$correct_idx]) ? $check['options'][$correct_idx] : '';
                $is_correct     = ($sel >= 0 && $sel === $correct_idx);

                if ($is_correct) {
                    $score++;
                }

                $results[] = [
                    'kind'           => 'micro',
                    'section'        => $si,
                    'section_title'  => $section['title'] ?? '',
                    'question'       => $check['question'],
                    'chosen'         => $chosen,
                    'correct_answer' => $correct_answer,
                    'is_correct'     => $is_correct,
                    'answered'       => $sel >= 0,
                ];
            }

            $quiz = $section['quiz'] ?? null;
            if (!is_array($quiz) || empty($quiz['question'])) {
                continue; // no checkpoint on this section
            }
            $total++;

            $type  = $quiz['type'] ?? 'mcq';
            $entry = $answers[$si . ':q'] ?? [];

            if ($type === 'arrange') {
                $tokens = $quiz['tokens'] ?? [];
                $built  = array_map(function ($idx) use ($tokens) {
                    return isset($tokens[(int) $idx]) ? $tokens[(int) $idx] : '';
                }, $entry['built'] ?? []);
                $expected       = $quiz['correctOrder'] ?? [];
                $is_correct     = !empty($built) && $built === $expected;
                $chosen         = implode(' ', $built);
                $correct_answer = implode(' ', $expected);
                $answered       = !empty($built);
            } elseif ($type === 'type') {
                $raw            = (string) ($entry['text'] ?? '');
                $accepted       = $quiz['acceptedAnswers'] ?? [];
                $is_correct     = false;
                foreach ($accepted as $a) {
                    if ($normalize($a) === $normalize($raw)) {
                        $is_correct = true;
                        break;
                    }
                }
                $chosen         = trim($raw);
                $correct_answer = $accepted[0] ?? '';
                $answered       = $chosen !== '';
            } else { // mcq
                $options        = $quiz['options'] ?? [];
                $sel            = isset($entry['sel']) ? (int) $entry['sel'] : -1;
                $correct_idx    = (int) ($quiz['correct'] ?? -1);
                $chosen         = ($sel >= 0 && isset($options[$sel])) ? $options[$sel] : '';
                $correct_answer = isset($options[$correct_idx]) ? $options[$correct_idx] : '';
                $is_correct     = ($sel >= 0 && $sel === $correct_idx);
                $answered       = $sel >= 0;
            }

            if ($is_correct) {
                $score++;
            }

            $results[] = [
                'kind'           => 'checkpoint',
                'section'        => $si,
                'section_title'  => $section['title'] ?? '',
                'question'       => $quiz['question'],
                'chosen'         => $chosen,
                'correct_answer' => $correct_answer,
                'is_correct'     => $is_correct,
                'answered'       => $answered,
            ];
        }

        return ['score' => $score, 'total' => $total, 'results' => $results];
    }

    // Validates a plain lesson+quiz (iq_discussion) topic document. Shared by
    // InteractiveQuizController::upload_topic()/discussion() and
    // AdminController's paste-JSON path (manage_discussions and the
    // assessment-modal "Paste new JSON" flow) so there is exactly one
    // definition of what makes a discussion topic well-formed.
    public function validate_discussion(array $data): string
    {
        if (empty($data['title']) || !is_string($data['title'])) {
            return 'JSON must have a non-empty "title" string field.';
        }
        if (empty($data['sections']) || !is_array($data['sections'])) {
            return 'JSON must have a non-empty "sections" array.';
        }
        foreach ($data['sections'] as $section) {
            if (!empty($section['chunks'])) {
                return 'This topic is in the microlearning format (sections with "chunks") — use the Microlearning Quiz widget instead of Interactive Discussion/Quiz.';
            }
        }
        foreach ($data['sections'] as $i => $section) {
            $n = $i + 1;
            if (empty($section['title'])) {
                return "Section {$n} is missing a \"title\" field.";
            }
            if (!isset($section['lesson'])) {
                return "Section {$n} is missing a \"lesson\" field.";
            }
            if (!isset($section['quiz']) || $section['quiz'] === null) {
                continue;
            }
            if (!is_array($section['quiz']) || empty($section['quiz'])) {
                return "Section {$n} has an invalid \"quiz\" value; use null or omit it when there is no quiz.";
            }
            $q = $section['quiz'];
            if (empty($q['question'])) {
                return "Section {$n} quiz is missing a \"question\" field.";
            }
            if (empty($q['options']) || !is_array($q['options']) || count($q['options']) < 2) {
                return "Section {$n} quiz must have at least 2 \"options\".";
            }
            if (!isset($q['correct']) || !is_int($q['correct']) || $q['correct'] < 0 || $q['correct'] >= count($q['options'])) {
                return "Section {$n} quiz \"correct\" must be a valid option index.";
            }
        }
        return '';
    }

    // Validates a microlearning (iq_micro) topic document. Deliberately
    // stricter than validate_discussion() in the places the renderer actually
    // depends on (a checkpoint's shape must match its declared type) and
    // looser where the format allows it: `lesson` is optional (a checkpoint
    // can be pure question) and a section may be chunks-only with no
    // checkpoint at all.
    public function validate_micro(array $data): string
    {
        if (empty($data['title']) || !is_string($data['title'])) {
            return 'JSON must have a non-empty "title" string field.';
        }
        if (empty($data['sections']) || !is_array($data['sections'])) {
            return 'JSON must have a non-empty "sections" array.';
        }

        $has_chunks = false;

        foreach ($data['sections'] as $i => $section) {
            $n = $i + 1;
            if (empty($section['title'])) {
                return "Section {$n} is missing a \"title\" field.";
            }

            foreach ($section['chunks'] ?? [] as $ci => $chunk) {
                $has_chunks = true;
                $cn = $ci + 1;
                if (empty($chunk['text'])) {
                    return "Section {$n}, chunk {$cn} is missing a \"text\" field.";
                }
                $check = $chunk['check'] ?? null;
                if (!is_array($check) || empty($check['question'])) {
                    return "Section {$n}, chunk {$cn} is missing a \"check.question\" field.";
                }
                if (empty($check['options']) || !is_array($check['options']) || count($check['options']) < 2) {
                    return "Section {$n}, chunk {$cn} check must have at least 2 \"options\".";
                }
                if (!isset($check['correct']) || !is_int($check['correct'])
                    || $check['correct'] < 0 || $check['correct'] >= count($check['options'])) {
                    return "Section {$n}, chunk {$cn} check \"correct\" must be a valid option index.";
                }
            }

            if (empty($section['quiz'])) {
                continue;
            }
            if (!is_array($section['quiz'])) {
                return "Section {$n} has an invalid \"quiz\" value; use null or omit it when there is no checkpoint.";
            }

            $q    = $section['quiz'];
            $type = $q['type'] ?? 'mcq';

            if (empty($q['question'])) {
                return "Section {$n} checkpoint is missing a \"question\" field.";
            }

            if ($type === 'mcq') {
                if (empty($q['options']) || !is_array($q['options']) || count($q['options']) < 2) {
                    return "Section {$n} checkpoint (mcq) must have at least 2 \"options\".";
                }
                if (!isset($q['correct']) || !is_int($q['correct'])
                    || $q['correct'] < 0 || $q['correct'] >= count($q['options'])) {
                    return "Section {$n} checkpoint (mcq) \"correct\" must be a valid option index.";
                }
            } elseif ($type === 'arrange') {
                if (empty($q['tokens']) || !is_array($q['tokens'])) {
                    return "Section {$n} checkpoint (arrange) must have a non-empty \"tokens\" array.";
                }
                if (empty($q['correctOrder']) || !is_array($q['correctOrder'])) {
                    return "Section {$n} checkpoint (arrange) must have a non-empty \"correctOrder\" array.";
                }
                // The renderer builds the answer by tapping tokens, so an order
                // that isn't a permutation of the pool is unanswerable.
                $tokens = $q['tokens'];
                $order  = $q['correctOrder'];
                sort($tokens);
                sort($order);
                if ($tokens !== $order) {
                    return "Section {$n} checkpoint (arrange): \"correctOrder\" must be a rearrangement of \"tokens\" (same items, no extras or omissions).";
                }
            } elseif ($type === 'type') {
                if (empty($q['acceptedAnswers']) || !is_array($q['acceptedAnswers'])) {
                    return "Section {$n} checkpoint (type) must have a non-empty \"acceptedAnswers\" array.";
                }
            } else {
                return "Section {$n} checkpoint has unknown \"type\" \"{$type}\"; use mcq, arrange, or type.";
            }
        }

        if (!$has_chunks) {
            return 'No section has a "chunks" array — this topic is in the plain lesson+quiz format, so use the Interactive Discussion/Quiz widget instead of Microlearning Quiz.';
        }

        return '';
    }

    // Dispatches to validate_micro()/validate_discussion() by format string
    // ('micro' or 'discussion') — the shape save_assessment() already uses to
    // tell the two topic widgets apart (see AdminController::_iq_topic_format()).
    public function validate_structure(array $data, $format): string
    {
        return $format === 'micro' ? $this->validate_micro($data) : $this->validate_discussion($data);
    }

    // Grades an iq_discussion answers blob — one quiz per section, $picked
    // keyed by section index ({ i: {selected} }). Lesson-only sections carry
    // no quiz and are not graded.
    public function grade_discussion(array $sections, array $picked)
    {
        $score   = 0;
        $total   = 0;
        $results = [];

        foreach ($sections as $i => $section) {
            $quiz = $section['quiz'] ?? null;
            if (!is_array($quiz) || empty($quiz['question']) || empty($quiz['options'])) {
                continue; // lesson-only section — not graded
            }
            $total++;

            $sel            = isset($picked[$i]['selected']) ? (int) $picked[$i]['selected'] : -1;
            $correct_idx    = (int) ($quiz['correct'] ?? -1);
            $chosen         = ($sel >= 0 && isset($quiz['options'][$sel])) ? $quiz['options'][$sel] : '';
            $correct_answer = isset($quiz['options'][$correct_idx]) ? $quiz['options'][$correct_idx] : '';
            $is_correct     = ($sel >= 0 && $sel === $correct_idx);

            if ($is_correct) {
                $score++;
            }

            $results[] = [
                'section'        => $i,
                'section_title'  => $section['title'] ?? '',
                'question'       => $quiz['question'],
                'chosen'         => $chosen,
                'correct_answer' => $correct_answer,
                'is_correct'     => $is_correct,
                'answered'       => $sel >= 0,
            ];
        }

        return ['score' => $score, 'total' => $total, 'results' => $results];
    }
}
