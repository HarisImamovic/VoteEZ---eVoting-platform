<?php
require_once "BaseService.php";
require_once __DIR__ . "/../dao/VoteDao.php";
require_once __DIR__ . "/../dao/UserDao.php";
require_once __DIR__ . "/../dao/CandidateDao.php";

class VoteService extends BaseService {
    private $user_dao;
    private $candidate_dao;

    public function __construct() {
        parent::__construct(new VoteDao);
        $this->user_dao = new UserDao();
        $this->candidate_dao = new CandidateDao();
    }

    public function get_by_user_id($user_id) {
        return $this->dao->get_by_user_id($user_id);
    }

    public function submit_votes($user_id, $candidate_ids) {
        $user = $this->user_dao->get_by_id($user_id);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.', 'status' => 404];
        }
        if ($user['has_voted']) {
            return ['success' => false, 'error' => 'You have already voted.', 'status' => 422];
        }
        $candidate_ids = array_values(array_unique(array_map('intval', $candidate_ids)));
        if (count($candidate_ids) < 1 || count($candidate_ids) > 3) {
            return ['success' => false, 'error' => 'Select between 1 and 3 candidates.', 'status' => 400];
        }

        foreach ($candidate_ids as $candidate_id) {
            $this->dao->add(['user_id' => (int)$user_id, 'candidate_id' => $candidate_id]);
            $this->candidate_dao->increment_votes($candidate_id);
        }
        $this->user_dao->change_vote_status($user_id);

        return ['success' => true];
    }
}
