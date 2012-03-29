<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NewsBundle\Twig\Extension;

use Symfony\Component\Routing\Router;
use Sonata\NewsBundle\Model\TagManagerInterface;
use Sonata\NewsBundle\Model\CategoryManagerInterface;
use Sonata\NewsBundle\Model\BlogInterface;
use Sonata\NewsBundle\Model\PostInterface;

class NewsExtension extends \Twig_Extension
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var TagManagerInterface
     */
    private $tagManager;
    
    /**
     * @var CategoryManagerInterface
     */
    private $categoryManager;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param \Sonata\NewsBundle\Model\TagManagerInterface $tagManager
     * @param \Sonata\NewsBundle\Model\CategoryManagerInterface $categoryManager
     * @param \Sonata\NewsBundle\Model\BlogInterface $blog
     */
    public function __construct(Router $router, TagManagerInterface $tagManager, CategoryManagerInterface $categoryManager, BlogInterface $blog)
    {
        $this->router           = $router;
        $this->tagManager       = $tagManager;
        $this->categoryManager  = $categoryManager;
        $this->blog             = $blog;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'sonata_news_permalink'    => new \Twig_Function_Method($this, 'generatePermalink'),
            'sonata_news_link_tag_rss' => new \Twig_Function_Method($this, 'renderTagRss', array('is_safe' => array('html'))),
            'sonata_news_cloud_tag' => new \Twig_Function_Method($this, 'renderTagCloud', array('is_safe' => array('html'))),
            'sonata_news_cloud_category' => new \Twig_Function_Method($this, 'renderCategoryCloud', array('is_safe' => array('html')))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_news';
    }

    /**
     * @return string
     */
    public function renderTagRss()
    {
        $rss = array();
        foreach($this->tagManager->findBy(array('enabled' => true)) as $tag) {
            $rss[] = sprintf('<link href="%s" title="%s : %s" type="application/rss+xml" rel="alternate" />',
                $this->router->generate('sonata_news_tag', array('tag' => $tag->getSlug(), '_format' => 'rss'), true),
                $this->blog->getTitle(),
                $tag->getName()
            );
        }

        return implode("\n", $rss);
    }

    /**
     * @return string
     */
    public function renderTagCloud()
    {
        $cloud = array();
        foreach($this->tagManager->findBy(array('enabled' => true)) as $tag) {
            $cloud[] = sprintf('<li><a href="%s" rel="tag">%s</a></li>',
                $this->router->generate('sonata_news_tag', array('tag' => $tag->getSlug()), true),
                $tag->getName()
            );
        }

        return implode(" ", $cloud);
    }
    
    /**
     * @return string
     */
    public function renderCategoryCloud()
    {
        $cloud = array();
        foreach($this->categoryManager->findBy(array('enabled' => true)) as $category) {
            $cloud[] = sprintf('<li><a href="%s">%s</a></li>',
                $this->router->generate('sonata_news_category', array('category' => $category->getSlug()), true),
                $category->getName()
            );
        }

        return implode(" ", $cloud);
    }
    
    /**
     * @param \Sonata\NewsBundle\Model\PostInterface $post
     * @return string|Exception
     */
    public function generatePermalink(PostInterface $post)
    {
        return $this->blog->getPermalinkGenerator()->generate($post);
    }
}

