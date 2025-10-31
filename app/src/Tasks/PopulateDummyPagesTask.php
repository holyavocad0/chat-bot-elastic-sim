<?php

namespace App\Tasks;

use App\Services\ElasticsearchService;
use Page;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Versioned\Versioned;
use Symfony\Component\Console\Input\InputInterface;
use SilverStripe\PolyExecution\PolyOutput;

/**
 * Task to populate 10 dummy pages with content
 * Usage: sake dev/tasks/PopulateDummyPagesTask
 */
class PopulateDummyPagesTask extends BuildTask
{
    private static $segment = 'PopulateDummyPagesTask';

    protected string $title = 'Populate Dummy Pages';
    
    protected string $class_description = 'Creates 10 dummy pages with sample content and indexes them in Elasticsearch';

    /**
     * Sample page data
     */
    protected $dummyPages = [
        [
            'Title' => 'Welcome to Our Website',
            'MenuTitle' => 'Home',
            'Summary' => 'Learn about our company and what we do.',
            'Content' => '<h2>Welcome</h2><p>We are a leading company in technology solutions. Our mission is to provide innovative products and services that help businesses grow and succeed. With over 10 years of experience, we have helped hundreds of clients achieve their goals.</p><p>Browse our website to learn more about our services, team, and success stories.</p>',
            'MetaDescription' => 'Welcome to our website - your trusted partner in technology solutions.'
        ],
        [
            'Title' => 'About Our Company',
            'MenuTitle' => 'About',
            'Summary' => 'Discover our history, mission, and values.',
            'Content' => '<h2>About Us</h2><p>Founded in 2013, our company has been at the forefront of digital transformation. We believe in innovation, quality, and customer satisfaction. Our team of experts works tirelessly to deliver solutions that exceed expectations.</p><p>We specialize in web development, cloud solutions, and digital marketing. Our approach is collaborative and transparent, ensuring that our clients are always informed and involved in the process.</p>',
            'MetaDescription' => 'Learn about our company history, mission, and the values that drive us forward.'
        ],
        [
            'Title' => 'Our Services',
            'MenuTitle' => 'Services',
            'Summary' => 'Explore the comprehensive range of services we offer.',
            'Content' => '<h2>What We Offer</h2><p>We provide a full suite of services including:</p><ul><li>Web Development and Design</li><li>Mobile App Development</li><li>Cloud Infrastructure Setup</li><li>Digital Marketing and SEO</li><li>E-commerce Solutions</li><li>Custom Software Development</li></ul><p>Each service is tailored to meet your specific needs and business objectives.</p>',
            'MetaDescription' => 'Comprehensive services including web development, mobile apps, cloud solutions, and digital marketing.'
        ],
        [
            'Title' => 'Meet Our Team',
            'MenuTitle' => 'Team',
            'Summary' => 'Get to know the talented people behind our success.',
            'Content' => '<h2>Our Team</h2><p>Our team consists of passionate professionals with diverse backgrounds in technology, design, and business. We have developers, designers, project managers, and consultants who work together to deliver exceptional results.</p><p>Every team member brings unique skills and perspectives, creating a dynamic and innovative work environment. We believe in continuous learning and professional development.</p>',
            'MetaDescription' => 'Meet our talented team of developers, designers, and consultants who make it all happen.'
        ],
        [
            'Title' => 'Contact Us',
            'MenuTitle' => 'Contact',
            'Summary' => 'Get in touch with us for inquiries and support.',
            'Content' => '<h2>Contact Information</h2><p>We would love to hear from you! Whether you have a question about our services, need support, or want to discuss a project, our team is ready to help.</p><p><strong>Email:</strong> info@example.com<br><strong>Phone:</strong> +1 (555) 123-4567<br><strong>Address:</strong> 123 Tech Street, Silicon Valley, CA 94025</p><p>You can also reach us through our contact form or schedule a consultation.</p>',
            'MetaDescription' => 'Contact us via email, phone, or visit our office. We are here to help with all your inquiries.'
        ],
        [
            'Title' => 'Blog: Latest Technology Trends',
            'MenuTitle' => 'Blog',
            'Summary' => 'Stay updated with the latest trends in technology and development.',
            'Content' => '<h2>Technology Trends 2024</h2><p>The technology landscape is constantly evolving. In this blog post, we explore the top trends shaping the industry including artificial intelligence, machine learning, cloud computing, and cybersecurity.</p><p>AI and machine learning are becoming integral to business operations, automating tasks and providing valuable insights. Cloud computing continues to offer scalability and flexibility, while cybersecurity remains a top priority for organizations worldwide.</p>',
            'MetaDescription' => 'Explore the latest technology trends including AI, machine learning, cloud computing, and cybersecurity.'
        ],
        [
            'Title' => 'Case Study: E-commerce Success',
            'MenuTitle' => 'Case Studies',
            'Summary' => 'Read about how we helped a retail company transform their online presence.',
            'Content' => '<h2>Client Success Story</h2><p>One of our clients, a mid-sized retail company, approached us to revamp their e-commerce platform. They were experiencing slow load times, poor user experience, and low conversion rates.</p><p>We designed and developed a modern, responsive website with optimized performance. We also implemented advanced analytics and integrated their inventory management system. The results were impressive: a 150% increase in online sales and a 40% improvement in customer satisfaction scores.</p>',
            'MetaDescription' => 'Case study: How we helped a retail company increase online sales by 150% through website optimization.'
        ],
        [
            'Title' => 'Careers: Join Our Team',
            'MenuTitle' => 'Careers',
            'Summary' => 'Explore career opportunities and become part of our growing team.',
            'Content' => '<h2>Work With Us</h2><p>We are always looking for talented individuals to join our team. If you are passionate about technology, innovation, and making a difference, we want to hear from you.</p><p>We offer competitive salaries, flexible work arrangements, professional development opportunities, and a supportive work culture. Current openings include positions in software development, UX design, project management, and digital marketing.</p>',
            'MetaDescription' => 'Join our team! Explore career opportunities in software development, design, and project management.'
        ],
        [
            'Title' => 'FAQ: Frequently Asked Questions',
            'MenuTitle' => 'FAQ',
            'Summary' => 'Find answers to common questions about our services and processes.',
            'Content' => '<h2>Common Questions</h2><p><strong>Q: How long does a typical project take?</strong><br>A: Project timelines vary based on scope and complexity, typically ranging from 4-16 weeks.</p><p><strong>Q: Do you offer ongoing support?</strong><br>A: Yes, we provide maintenance and support packages for all our clients.</p><p><strong>Q: What technologies do you work with?</strong><br>A: We work with modern web technologies including React, Vue.js, Node.js, PHP, and various cloud platforms.</p>',
            'MetaDescription' => 'Frequently asked questions about our services, project timelines, technologies, and support options.'
        ],
        [
            'Title' => 'Privacy Policy',
            'MenuTitle' => 'Privacy',
            'Summary' => 'Learn how we protect your data and respect your privacy.',
            'Content' => '<h2>Privacy Policy</h2><p>We are committed to protecting your privacy and personal information. This policy explains how we collect, use, and safeguard your data.</p><p>We collect only necessary information and never share it with third parties without your consent. All data is stored securely using industry-standard encryption. You have the right to access, modify, or delete your personal information at any time.</p><p>For questions about our privacy practices, please contact our data protection officer.</p>',
            'MetaDescription' => 'Our privacy policy explains how we collect, use, and protect your personal information.'
        ]
    ];
    public function execute(InputInterface $input, PolyOutput $output) : int
    {
        $output->write("Starting to create dummy pages...\n\n");

        $count = 0;
        $elasticService = ElasticsearchService::singleton();

        // Create or recreate the Elasticsearch index
        echo "Creating Elasticsearch index...\n";
        $elasticService->createIndex();
        echo "Index created successfully.\n\n";

        foreach ($this->dummyPages as $pageData) {
            // Check if page already exists
            $existingPage = Page::get()->filter('Title', $pageData['Title'])->first();

            if ($existingPage) {
                echo "Page '{$pageData['Title']}' already exists (ID: {$existingPage->ID}). Skipping...\n";
                continue;
            }

            // Create new page
            $page = Page::create();
            $page->Title = $pageData['Title'];
            $page->MenuTitle = $pageData['MenuTitle'];
            $page->Summary = $pageData['Summary'];
            $page->Content = $pageData['Content'];
            $page->MetaDescription = $pageData['MetaDescription'];
            $page->ShowInMenus = true;

            // Save to draft
            $page->write();

            // Publish the page
            $page->publishRecursive();

            echo "Created and published page: {$page->Title} (ID: {$page->ID})\n";

            // Index in Elasticsearch
            $data = [
                'id' => $page->ID,
                'title' => $page->Title,
                'content' => strip_tags($page->Content),
                'summary' => $page->Summary,
                'menu_title' => $page->MenuTitle,
                'meta_description' => $page->MetaDescription,
                'url' => $page->AbsoluteLink(),
                'last_edited' => $page->LastEdited,
                'created' => $page->Created,
            ];

            $elasticService->indexDocument($page->ID, $data);
            echo "Indexed in Elasticsearch.\n\n";

            $count++;
        }

        echo "\n========================================\n";
        echo "Task completed!\n";
        echo "Total pages created: {$count}\n";
        echo "All pages have been indexed in Elasticsearch.\n";
        echo "========================================\n";
        return 1;
    }
}
