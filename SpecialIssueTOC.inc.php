<?php

import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.specialIssueTOC.classes.specialIssueDAO');


class SpecialIssueTOCPlugin extends GenericPlugin {

  public function register($category, $path, $mainContextId = NULL) {

    // Register the plugin even when it is not enabled
    $success = parent::register($category, $path, $mainContextId);

    if ($success && $this->getEnabled($mainContextId)) {
      HookRegistry::register('Templates::Common::Footer::PageFooter', array(&$this, 'addSpecialIssueArticles'));
    }
    return $success;
  }



  /**
   * Provide a name for this plugin
   *
   * The name will appear in the plugins list where editors can
   * enable and disable plugins.
   */
  public function getDisplayName() {
    return __('plugins.generic.specialIssueTOC.name');
  }

  /**
   * Provide a description for this plugin
   *
   * The description will appear in the plugins list where editors can
   * enable and disable plugins.
   */
  public function getDescription() {
    return __('plugins.generic.specialIssueTOC.description');
  }

  /**
   * Testing from PluginTemplate
   */
  function addSpecialIssueArticles($hookName, $params) {
    $templateMgr =& $params[1];
    $output =& $params[2];
    $issue = $templateMgr->get_template_vars('issue');
    $vol = $issue->getVolume();
    $nr = $issue->getNumber();
    $descr = $issue->getLocalizedDescription();

    $request = Application::get()->getRequest();
    $baseUrl = $request->getRouter()->url($request,NULL,'article','view');

    // Continue only when there is a description
    if (!$descr) { 
      return false; 
    }

    // Get all the articles with the necessary subject
    $articles = $this->getMyIssueArticles('v'.$vol.'i'.$nr.'n');
    
    // header for article list
    $text_block = '<div class="section">';
    $text_block .= '<h2>Articles</h2>';
    $text_block .= '<ul class="cmp_article_list articles">';


    foreach($articles as $publicationId) { 
      // get the data for the publication
      $publication = Services::get('publication')->get($publicationId);  // This returns the basic data of the publication.
      $info = $this->getInfoBlock($publication, $baseUrl);               // collects the publication info into a block of HTML for one publication
      $text_block .= $info;                                              // collect the HTML blocks of all publications 
    }

    // footer for articles list
    $text_block .= '</ul></div>';
    // embed the text_block in a JavaScript call
    $text_block = $this->wrapInJavaScript($text_block);
    $output .= $text_block;
    return false;
  }

  private function getMyIssueArticles($searchString) {
//    $listArticles = array();
    $specialIssueDAO = new SpecialIssueDAO();
    $myIssueArticles = $specialIssueDAO->getMyIssueArticles($searchString.'%');
    return $myIssueArticles;
  }

  private function getAuthorsString($publication) {
    $authors = $publication->getData('authors');
    $authors_array = array();
    foreach($authors as $author) {
      $lastName = $author->getData('familyName')['en_US'];
      $firstName = $author->getData('givenName')['en_US'];
      $authors_array[] = "$firstName $lastName";
    }
    $authors_string = join(", ", $authors_array);
    return $authors_string;
  }

  private function getInfoBlock($publication, $baseUrl) {
    $title_array = $publication->getData('title');   // Extracting the title
    $title = $title_array['en_US'];
    $submID = $publication->getData('submissionId');   // Extracting the submission ID
    $publID = $publication->getData('id');             // Extracting the publication ID // WRONG!
    $publID = $this->getPublicationId($publication);             // Extracting the publication ID 
    $authors_string = $this->getAuthorsString($publication);

    $info =  '<li>';
    $info .= '  <div class="obj_article_summary">';
    $info .= '    <h3 class="title">';
    $info .= '      <a id="article-'.$submID.'"'; 
    $info .= '        href="'.$baseUrl.'/'.$submID.'">'.$title;
    $info .= '      </a>';
    $info .= '    </h3>';

    $info .= '    <div class="meta">';
    $info .= '      <div class="authors">'.$authors_string.'</div>';
    $info .= '    </div>';

    $info .= '    <ul class="galleys_links">';
    $info .= '      <li>';
    $info .= '        <a class="obj_galley_link pdf"'; 
    $info .= '          href="'.$baseUrl.'/'.$submID.'/'.$publID.'"'; 
    $info .= '          aria-labelledby=article-'.$submID.'>';
    $info .= '          PDF';
    $info .= '        </a>';
    $info .= '      </li>';
    $info .= '    </ul>';

    $info .= '  </div>';
    $info .= '</li>';

    return $info;
  }

  function getPublicationId($publication){
    $galleys = $publication->getData('galleys');
    foreach ($galleys as $galley){
      $label = $galley->getData('label');
      $id = $galley->getData('id');
      if ($label == 'PDF') { return $id; }
    }
    return false;
  }

  private function wrapInJavaScript($text_block) {
    $js = '<script>
             document.addEventListener("DOMContentLoaded", function(){
               const collection = document.getElementsByClassName("sections");
               collection[0].innerHTML += \''.$text_block.'\';
             });
           </script>';
    return $js;
  }
}

