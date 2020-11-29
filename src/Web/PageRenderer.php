<?php

namespace Web;

use Http\Request;

class PageRenderer {

    private const TEMPLATES = [
        "navbar" => "static/html/navbar.html",
        "login" => "static/html/index.html",
        "homepage" => "static/html/public.html",
        "head" => "static/html/head.html"
    ];

    private $targetPage;
    private $requestContext;
    private $requestArgs;
    private $targetTemplate;

    public function __construct($pageName, Request $requestContext, $args) {
        $this->targetPage = $pageName;
        $this->requestContext = $requestContext;
        $this->requestArgs = $args;
    }

    static function renderHomePage(Request $request, $args) {
        // render the home page
        $renderer = new PageRenderer("homepage", $request, $args);
        $renderer->renderPage();
        $renderer->finish();
    }


    static function renderLogIn(Request $request, $args) {
        $renderer = new PageRenderer("login", $request, $args);
        $renderer->renderPage();
        $renderer->finish();
    }

    private function renderPage() {
        $this->targetTemplate = $this->renderTemplate($this->targetPage);
    }

    private function renderTemplate($templateName): string {
        $template = $this->readTemplate($templateName);
        return $this->buildTemplate($template);
    }

    private function readTemplate($templateName): string {
        $fileName = __DIR__ . '/../../' . self::TEMPLATES[$templateName];
        $openTemplate = fopen($fileName, 'r') or die("Webpage unavailable");
        $template = fread($openTemplate, filesize($fileName));
        fclose($openTemplate);
        return $template;
    }

    private function buildTemplate($template): string {
        $templateMatches = [];
        $functionMatches = [];
        while (
            preg_match("/{%T ([a-zA-Z]+) %T}/", $template, $templateMatches, PREG_OFFSET_CAPTURE) ||
            preg_match("/{%F ([a-zA-Z]+) %F}/", $template, $functionMatches, PREG_OFFSET_CAPTURE)
        ) {
            if (sizeof($templateMatches) > 0) {
                // found a template
                // recursively build the template
                $builtTemplate = $this->renderTemplate($templateMatches[1][0]);
                // insert the sub-template into the template
                $template = substr_replace($template, $builtTemplate, $templateMatches[0][1], strlen($templateMatches[0][0]));
            } else {
                $method = $functionMatches[1][0];
                $functionResult = $this->$method();
                $template = substr_replace($template, $functionResult, $functionMatches[0][1], strlen($functionMatches[0][0]));
            }
            // reset
            $templateMatches = [];
            $functionMatches = [];
        }

        return $template;
    }

    private function finish() {
        header('Content-Type: text/html');
        echo $this->targetTemplate;
    }

    private function loginOrLogout() {
        [$route, $label] = (isset($_SESSION['userId'])) ? ["/logout", "LOG OUT"] : ["/login", "LOG IN"];
        $lastItem = <<<EOD
<li class="nav-item">
                    <a class="nav-link" href="$route">$label</a>
                </li>
EOD;
        return $lastItem;
    }
}

