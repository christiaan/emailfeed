<?php
namespace Christiaan\EmailFeed;

use Fetch\Server;
use Suin\RSSWriter\Channel;
use Fetch\Message;
use Suin\RSSWriter\Item;
use Suin\RSSWriter\Feed;

class EmailFeedGenerator
{
    private $mailServer;
    private $feed;
    private $channel;
    private $dir;
    private $url;

    public function __construct(Server $mailServer, Feed $feed, Channel $channel)
    {
        $this->mailServer = $mailServer;
        $this->feed = $feed;
        $this->channel = $channel;
    }

    public function createFeed($dir, $filename, $url, $limit)
    {
        $this->dir = $dir;
        $this->url = $url;
        if (!is_dir($this->dir)) {
            throw new Exception('Dir does not exist', Exception::NONEXISTING_DIR);
        }

        if (!is_writable($this->dir)) {
            throw new Exception('Dir not writable', Exception::NOT_WRITABLE);
        }

        $since = date('j F Y', strtotime($limit));

        /* @var $message Message */
        foreach ($this->mailServer->search('ALL SINCE "' . $since . '"') as $message) {
            $this->createItem($message);
        }

        file_put_contents($this->dir . '/' . $filename, (string) $this->feed->render());
    }

    private function createItem(Message $message)
    {
        $item = new Item();
        $item->title(utf8_encode($message->getSubject()))
            ->description(utf8_encode($message->getMessageBody()))
            ->url($this->url . $message->getUid() . '.html')
            ->pubDate($message->getDate())
            ->guid($message->getUid())
            ->appendTo($this->channel);

        file_put_contents($this->dir . '/' . $message->getUid() . '.html', $message->getMessageBody(true));
    }
}
