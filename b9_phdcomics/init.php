<?php
class B9_PhdComics extends Plugin {
	//works with http://www.phdcomics.com/gradfeed_justcomics.php

        private $link;
        private $host;

        function about() {
                return array(1.0,
                        "Strip unnecessary stuff from phdcomics feeds",
                        "b9.cl");
        }

        function init($host) {
                $this->link = $host->get_link();
                $this->host = $host;

                $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
        }

        function hook_article_filter($article) {
                $owner_uid = $article["owner_uid"];

                if (strpos($article["guid"], "phdcomics.com") !== FALSE) {
                        if (strpos($article["plugin_data"], "phdcomics,$owner_uid:") === FALSE) {
                                $doc = new DOMDocument();
                                @$doc->loadHTML(fetch_file_contents($article["link"]));

                                $basenode = false;

                                if ($doc) {
                                        $xpath = new DOMXPath($doc);
                                        $entries = $xpath->query('(//img[@src])'); // we might also check for img[@class='strip'] I guess...

                                        $matches = array();

                                        foreach ($entries as $entry) {

                                                if (preg_match("/(http:\/\/www.phdcomics.com\/comics\/.*)/i", $entry->getAttribute("src"), $matches)) {

                                                        $entry->setAttribute("src", $matches[0]);
                                                        $basenode = $entry;
                                                        break;
                                                }
                                        }

                                        if ($basenode) {
                                                $article["content"] = $doc->saveXML($basenode);
                                                $article["plugin_data"] = "phdcomics,$owner_uid:" . $article["plugin_data"];
                                        }
                                }
                        } else if (isset($article["stored"]["content"])) {
                                $article["content"] = $article["stored"]["content"];
                        }
                }

                return $article;
        }
}
?>
