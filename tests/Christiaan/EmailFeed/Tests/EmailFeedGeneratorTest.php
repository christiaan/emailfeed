<?php
namespace Christiaan\EmailFeed\Tests;

use PHPUnit_Framework_TestCase;
use Christiaan\EmailFeed\Exception;
use Christiaan\EmailFeed\EmailFeedGenerator;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Channel;
use Fetch\Message;

/**
 * @covers \Christiaan\EmailFeed\EmailFeedGenerator
 */
class EmailFeedGeneratorTest extends PHPUnit_Framework_TestCase
{
    /** @var EmailFeedGenerator */
    private $obj;
    /** @var \Fetch\Server|\PHPUnit_Framework_MockObject_MockObject */
    private $server;
    /** @var Feed */
    private $feed;
    /** @var Channel */
    private $channel;

    protected function setUp()
    {
        $this->server = $this->getMockBuilder('Fetch\Server')->disableOriginalConstructor()->getMock();
        $this->feed = new Feed();
        $this->channel = new Channel();
        $this->obj = new EmailFeedGenerator($this->server, $this->feed, $this->channel);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionCode 1
     */
    public function testInvalidDirThrowsException()
    {
        $this->obj->createFeed('/nonexistin/dir', 'filename', 'url', 10);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 2
     */
    public function testNonWritableDirThrowsException()
    {
        $this->obj->createFeed('/root', 'filename', 'url', 10);
    }

    public function testEmptyFeed()
    {
        $limit = 10;
        $since = date('j F Y', strtotime($limit));
        $this->server
            ->expects($this->any())
            ->method('search')
            ->with('ALL SINCE "' . $since . '"')
            ->will($this->returnValue(array()));

        $this->obj->createFeed('/tmp', 'feed.xml', 'http://www.example.com', $limit);

        $this->assertFileExists('/tmp/feed.xml');
        @unlink('/tmp/feed.xml');
    }

    public function testFilledFeed()
    {
        $time = time();

        $messages = array(
            $this->createMessage('message1', 'Eerste bericht', 1, $time),
            $this->createMessage('message2', 'Tweede bericht', 2, $time),
            $this->createMessage('message3', 'Derde bericht', 3, $time)
        );
        $this->server
            ->expects($this->any())
            ->method('search')
            ->will($this->returnValue($messages));

        $this->obj->createFeed('/tmp', 'feed.xml', 'http://www.example.com', 3);

        $files = array(
            'feed.xml', '1.html', '2.html', '3.html'
        );

        foreach ($files as $file) {
            $this->assertFileEquals(__DIR__.'/'.$file, '/tmp/'.$file);
            @unlink('/tmp/'.$file);
        }
    }

    private function createMessage($subject, $body, $uid, $date)
    {
        $message = $this->getMockBuilder('Fetch\Message')->disableOriginalConstructor()->getMock();
        $message->expects($this->any())->method('getSubject')->will($this->returnValue($subject));
        $message->expects($this->any())->method('getMessageBody')->will($this->returnValue($body));
        $message->expects($this->any())->method('getUid')->will($this->returnValue($uid));
        $message->expects($this->any())->method('getDate')->will($this->returnValue($date));

        return $message;
    }
}
