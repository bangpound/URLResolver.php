<?php

class URLResolverResult
{
    private $url;
    private $status;
    private $content_type;
    private $content_length;

    private $is_starting_point = false;
    private $is_open_graph = false;
    private $is_canonical = false;

    private $redirect;
    private $redirect_is_open_graph = false;
    private $redirect_is_canonical = false;

    private $failed = false;
    private $error = false;
    private $error_message = '';

    public function __construct($url)
    {
        $this->url = $url;
    }

    # This is the best resolved URL we could obtain after following redirects.
    public function getURL() { return $this->url; }

    # Returns the integer [HTTP status code] for the resolved URL.
    # Examples: 200: OK (success), 404: Not Found, 301: Moved Permanently, ...
    public function getHTTPStatusCode() { return $this->status; }
    public function setHTTPStatusCode($status) { $this->status = $status; }

    # Returns _true_ if the [HTTP status code] for the resolved URL is 200.
    public function hasSuccessHTTPStatus() { return ($this->status == 200); }

    # Returns _true_ if the [HTTP status code] for the resolved URL is 301 or 302.
    public function hasRedirectHTTPStatus() { return ($this->status == 301 || $this->status == 302); }

    # Returns the value of the Content-Type [HTTP header] for the resolved URL.
    # If header not provided, _null_ is returned. Examples: text/html, image/jpeg, ...
    public function getContentType() { return $this->content_type; }
    public function setContentType($type) { $this->content_type = $type; }
    public function hasHTMLContentType($type=null)
    {
        if (!isset($type)) { $type = $this->content_type; }

        return (stripos($type, 'html') !== false);
    }

    # Returns the size of the fetched URL in bytes for the resolved URL.
    # Determined only by the Content-Length [HTTP header]. _null_ returned otherwise.
    public function getContentLength() { return $this->content_length; }
    public function setContentLength($length) { $this->content_length = $length; }

    # Returns true if resolved URL was marked as the Open Graph URL (og:url)
    public function isOpenGraphURL($value=null)
    {
        if (isset($value)) { $this->is_open_graph = $value ? true : false; }

        return $this->is_open_graph;
    }

    # Returns true if resolved URL was marked as the Canonical URL (rel=canonical)
    public function isCanonicalURL($value=null)
    {
        if (isset($value)) { $this->is_canonical = $value ? true : false; }

        return $this->is_canonical;
    }

    # Returns true if resolved URL was also the URL you passed to resolveURL().
    public function isStartingURL($value=null)
    {
        if (isset($value)) { $this->is_starting_point = $value ? true : false; }

        return $this->is_starting_point;
    }

    # Returns true if an error occurred while resolving the URL.
    # If this returns false, $url_result is guaranteed to have a status code.
    public function didErrorOccur()
    {
        return ($this->error || $this->failed);
    }

    # Returns an explanation of what went wrong if didErrorOccur() returns true.
    public function getErrorMessageString()
    {
        return ($this->error || $this->failed) ? $this->error_message : '';
    }

    # Returns _true_ if there was a connection error (no header or no body returned).
    # May indicate a situation where you are more likely to try at least once more.
    # If this returns _true_, didErrorOccur() will true as well.
    public function didConnectionFail($value=null, $message=null)
    {
        if (isset($value)) {
            $this->failed = $value ? true : false;
            $this->error_message = $message;
        }

        return $this->failed;
    }

    public function didFatalErrorOccur($value=null, $message=null)
    {
        if (isset($value)) {
            $this->error = $value ? true : false;
            $this->error_message = $message;
        }

        return $this->error;
    }

    public function getRedirectTarget() { return $this->redirect; }
    public function setRedirectTarget($url) { $this->redirect = $url; }

    public function redirectTargetIsOpenGraphURL($value=null)
    {
        if (isset($value)) { $this->redirect_is_open_graph = $value ? true : false; }

        return $this->redirect_is_open_graph;
    }

    public function redirectTargetIsCanonicalURL($value=null)
    {
        if (isset($value)) { $this->redirect_is_canonical = $value ? true : false; }

        return $this->redirect_is_canonical;
    }

    public function debugStatus()
    {
        $attr = array();
        if ($this->failed || $this->error) { array_push($attr, 'ERROR'); }
        if ($this->is_open_graph) { array_push($attr, 'og:url'); }
        if ($this->is_canonical) { array_push($attr, 'rel=canonical'); }

        $status = '(' . $this->status;
        if (count($attr)) { $status .= '; ' . join(', ', $attr); }
        $status .= ')';

        return $status;
    }
}
