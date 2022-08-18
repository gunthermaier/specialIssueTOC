<?php

import('lib.pkp.classes.db.DAO');

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Collection;

class SpecialIssueDAO extends DAO {

  public function getMyIssueArticles($searchString) {
    $result = Capsule::table('submission_search_keyword_list')
    ->join('submission_search_object_keywords', 'submission_search_keyword_list.keyword_id', '=', 'submission_search_object_keywords.keyword_id')
    ->join('submission_search_objects', 'submission_search_object_keywords.object_id', '=', 'submission_search_objects.object_id')
    ->join('publications', 'submission_search_objects.submission_id', '=', 'publications.submission_id')
    ->where('keyword_text', 'like', $searchString)
    ->select('publication_id')
    ->orderby('keyword_text')
    ->get();

//    $result = Capsule::table('submission_search_keyword_list')
//    ->join('submission_search_object_keywords', 'submission_search_keyword_list.keyword_id', '=', 'submission_search_object_keywords.keyword_id')
//    ->join('submission_search_objects', 'submission_search_object_keywords.object_id', '=', 'submission_search_objects.object_id')
//    ->where('keyword_text', 'like', $searchString)
//    ->select('submission_id')
//    ->orderby('keyword_text')
//    ->get();

    $myIssueArticles = [];
    foreach ($result->toArray() as $row) {
//      $myIssueArticles[] = $row->submission_id;
      $myIssueArticles[] = $row->publication_id;
    }
    return $myIssueArticles;
  }

}