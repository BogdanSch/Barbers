<?php // algolplus

class SLN_Shortcode_SalonRecentComments
{
    const NAME = 'sln_recent_comments';

    private $plugin;
    private $attrs;

    function __construct(SLN_Plugin $plugin, $attrs)
    {
        $this->plugin = $plugin;
        $this->attrs = $attrs;
    }

    public static function init(SLN_Plugin $plugin)
    {
        add_shortcode(self::NAME, array(__CLASS__, 'create'));
    }

    public static function create($attrs)
    {
        SLN_TimeFunc::startRealTimezone();

        $obj = new self(SLN_Plugin::getInstance(), $attrs);

        $ret = $obj->execute();
        SLN_TimeFunc::endRealTimezone();
        return $ret;
    }

    public function execute()
    {
        $number = 10;
        $rating = false;

        if(!empty($this->attrs['number'])){
            $number = (int)$this->attrs['number'];
        }
        if(!empty($this->attrs['rating'])){
            $rating = (int)$this->attrs['rating'];
        }

        global $wpdb;

        $where = $rating ? "AND pm.meta_value = '" . $rating . "'" : "";

        $results = $wpdb->get_results(
            $wpdb->prepare("
                SELECT
                    c.*,
                    pm.meta_value as rating
                FROM {$wpdb->comments} c
                INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID AND p.post_type = 'sln_booking'
                LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sln_booking_rating'
                WHERE
                    c.comment_type = 'sln_review'
                AND
                    p.post_status <> 'trash'
                {$where}
                ORDER BY
                    c.comment_date DESC
                LIMIT %d
            ", $number)
        );

        $data = array('comments' => $results);

        return $this->render($data);
    }

    protected function render($data = array())
    {
        return $this->plugin->loadView('shortcode/sln_recent_comments', compact('data'));
    }

}
