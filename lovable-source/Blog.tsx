import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowRight } from "lucide-react";
import { useState } from "react";
import { toast } from "@/hooks/use-toast";
import { addContactToBrevo } from "@/services/brevoService";
import { checkForSpam } from "@/utils/spamFilter";

const Blog = () => {
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [bottomEmail, setBottomEmail] = useState("");
  const [bottomIsSubmitting, setBottomIsSubmitting] = useState(false);

  const handleNewsletterSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
      toast({
        title: "Please enter a valid email",
        description: "We need a valid email to subscribe you to our newsletter",
        variant: "destructive",
      });
      return;
    }

    // Spam filtering
    const spamCheck = checkForSpam(email);
    
    if (spamCheck.isSpam) {
      console.log('Spam detected in blog header newsletter:', spamCheck);
      toast({
        title: "Invalid email",
        description: "Please enter a valid business email address.",
        variant: "destructive",
      });
      return;
    }

    setIsSubmitting(true);
    
    try {
      const success = await addContactToBrevo({
        email,
        attributes: {
          LEAD_SOURCE: "Newsletter Blog Header"
        },
        listIds: [3] // Newsletter list
      });

      if (success) {
        toast({
          title: "Welcome aboard! 🚀",
          description: "You've joined 700+ members getting growth insights.",
          variant: "default",
        });
        setEmail("");
      } else {
        throw new Error("Failed to add contact");
      }
    } catch (error) {
      console.error("Error submitting newsletter:", error);
      toast({
        title: "Something went wrong",
        description: "Please try again or contact support.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleBottomNewsletterSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!bottomEmail || !/^\S+@\S+\.\S+$/.test(bottomEmail)) {
      toast({
        title: "Please enter a valid email",
        description: "We need a valid email to subscribe you to our newsletter",
        variant: "destructive",
      });
      return;
    }

    // Spam filtering
    const spamCheck = checkForSpam(bottomEmail);
    
    if (spamCheck.isSpam) {
      console.log('Spam detected in blog footer newsletter:', spamCheck);
      toast({
        title: "Invalid email",
        description: "Please enter a valid business email address.",
        variant: "destructive",
      });
      return;
    }

    setBottomIsSubmitting(true);
    
    try {
      const success = await addContactToBrevo({
        email: bottomEmail,
        attributes: {
          LEAD_SOURCE: "Newsletter Blog Footer"
        },
        listIds: [3] // Newsletter list
      });

      if (success) {
        toast({
          title: "Welcome aboard! 🚀",
          description: "You've joined 700+ members getting growth insights.",
          variant: "default",
        });
        setBottomEmail("");
      } else {
        throw new Error("Failed to add contact");
      }
    } catch (error) {
      console.error("Error submitting newsletter:", error);
      toast({
        title: "Something went wrong",
        description: "Please try again or contact support.",
        variant: "destructive",
      });
    } finally {
      setBottomIsSubmitting(false);
    }
  };

  const blogPosts = [
    {
      id: "what-is-ai-marketing",
      title: "What is AI marketing? (And why it's not just hype)",
      excerpt: "AI marketing isn't just a buzzword—it's a fundamental shift in how we understand and engage customers. Discover the data-driven strategies that are transforming business growth.",
      date: "January 15, 2025",
      readTime: "8 min read",
      category: "AI Marketing",
      image: "https://images.unsplash.com/photo-1677442136019-21780ecad995?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "what-is-ai-marketing"
    },
    {
      id: "biggest-growth-mistakes",
      title: "7 biggest growth mistakes premium brands make",
      excerpt: "Premium brands often fall into growth traps that commoditize their positioning. Learn the 7 critical mistakes and how to avoid them while scaling sustainably.",
      date: "January 20, 2025",
      readTime: "10 min read",
      category: "Brand Strategy",
      image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "7-biggest-growth-mistakes-premium-brands"
    },
    {
      id: "traditional-marketing-fail",
      title: "Why traditional marketing strategies fail in 2025",
      excerpt: "The marketing playbook from 2020 is obsolete. Consumer behavior, technology, and market dynamics have fundamentally shifted. Here's what works now.",
      date: "January 25, 2025",
      readTime: "12 min read",
      category: "Marketing Strategy",
      image: "https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "traditional-marketing-strategies-fail-2025"
    },
    {
      id: "ai-automation-marketing-team",
      title: "How AI automation strengthens (not replaces) your marketing team",
      excerpt: "AI isn't here to replace marketers—it's here to amplify their capabilities. Discover how smart automation can free your team to focus on strategy and creativity.",
      date: "January 28, 2025",
      readTime: "9 min read",
      category: "AI Marketing",
      image: "https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "ai-automation-strengthen-marketing-team"
    },
    {
      id: "future-content-ai-creative",
      title: "The future of content: AI as your creative assistant",
      excerpt: "Content creation is evolving beyond human vs. AI to human + AI collaboration. See how leading brands are using AI to enhance creativity, not replace it.",
      date: "February 1, 2025",
      readTime: "11 min read",
      category: "Content Strategy",
      image: "https://images.unsplash.com/photo-1555421689-d68471e189f2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "future-content-ai-creative-assistant"
    },
    {
      id: "branding-performance-marketing",
      title: "Integrating branding with performance marketing for sustainable growth",
      excerpt: "The false choice between brand building and performance marketing is killing long-term growth. Learn how to integrate both for sustainable, profitable scaling.",
      date: "February 5, 2025",
      readTime: "13 min read",
      category: "Brand Strategy",
      image: "https://images.unsplash.com/photo-1611224923853-80b023f02d71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "branding-performance-marketing-integration"
    },
    {
      id: "ai-marketing-b2b",
      title: "AI marketing for B2B: opportunities, risks, and real results",
      excerpt: "B2B marketing is ripe for AI transformation, but the stakes are higher. Explore proven AI strategies that work in complex B2B sales cycles.",
      date: "February 8, 2025",
      readTime: "14 min read",
      category: "B2B Marketing",
      image: "https://images.unsplash.com/photo-1559136555-9303baea8ebd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "ai-marketing-b2b-opportunities-risks-results"
    },
    {
      id: "low-hanging-growth-ai",
      title: "Low-hanging growth: what you can improve tomorrow using AI",
      excerpt: "You don't need a complete overhaul to see AI results. Discover 5 quick AI implementations that can improve your marketing performance within days.",
      date: "February 10, 2025",
      readTime: "7 min read",
      category: "Growth Hacking",
      image: "https://images.unsplash.com/photo-1518186285589-2f7649de83e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "low-hanging-growth-ai-improvements"
    },
    {
      id: "roi-optimization-netherlands",
      title: "ROI optimization for Netherlands SME: a data-driven approach",
      excerpt: "Dutch SMEs face unique market challenges. Learn data-driven ROI optimization strategies specifically designed for the Netherlands business landscape.",
      date: "February 12, 2025",
      readTime: "10 min read",
      category: "ROI Optimization",
      image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "roi-optimization-netherlands-sme"
    },
    {
      id: "marketing-funnel-2025",
      title: "Marketing funnel: how it works in 2025",
      excerpt: "The traditional marketing funnel is dead. Consumer journeys are now complex, multi-touch experiences. Here's how to map and optimize the modern funnel.",
      date: "February 15, 2025",
      readTime: "12 min read",
      category: "Funnel Strategy",
      image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "marketing-funnel-how-it-works-2025"
    },
    {
      id: "hidden-costs-inefficient-funnels",
      title: "The hidden costs of inefficient marketing funnels",
      excerpt: "Funnel inefficiencies don't just reduce conversions—they compound into massive hidden costs. Learn to identify and fix the leaks draining your budget.",
      date: "February 18, 2025",
      readTime: "9 min read",
      category: "Funnel Optimization",
      image: "https://images.unsplash.com/photo-1553729459-efe14ef6055d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "hidden-costs-inefficient-marketing-funnels"
    },
    {
      id: "customer-retention-ai-2025",
      title: "Customer retention AI strategies for 2025",
      excerpt: "Acquiring customers is expensive; retaining them is profitable. Discover AI-powered retention strategies that turn one-time buyers into lifetime advocates.",
      date: "February 20, 2025",
      readTime: "11 min read",
      category: "Customer Retention",
      image: "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "customer-retention-ai-strategies-2025"
    },
    {
      id: "marketing-automation-mistakes",
      title: "5 marketing automation mistakes that are losing you customers",
      excerpt: "Marketing automation should nurture relationships, not damage them. Avoid these 5 critical mistakes that turn prospects into unsubscribes.",
      date: "February 22, 2025",
      readTime: "8 min read",
      category: "Marketing Automation",
      image: "https://images.unsplash.com/photo-1485827404703-89b55fcc595e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "marketing-automation-mistakes-losing-customers"
    },
    {
      id: "social-media-roi-measurement",
      title: "Social media ROI measurement: beyond vanity metrics",
      excerpt: "Likes and follows don't pay the bills. Learn how to measure social media ROI with metrics that actually matter to your bottom line.",
      date: "February 25, 2025",
      readTime: "10 min read",
      category: "Social Media",
      image: "https://images.unsplash.com/photo-1611262588024-d12430b98920?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "social-media-roi-measurement-guide"
    },
    {
      id: "email-marketing-personalization",
      title: "Advanced email marketing personalization (beyond 'Hi [Name]')",
      excerpt: "True email personalization goes far beyond first names. Discover advanced personalization strategies that drive engagement and conversions.",
      date: "February 28, 2025",
      readTime: "9 min read",
      category: "Email Marketing",
      image: "https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "email-marketing-personalization-advanced"
    },
    {
      id: "content-marketing-distribution",
      title: "Content marketing distribution strategies that actually work",
      excerpt: "Great content without distribution is invisible content. Master the distribution strategies that get your content seen by the right audience.",
      date: "March 1, 2025",
      readTime: "11 min read",
      category: "Content Marketing",
      image: "https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "content-marketing-distribution-strategies"
    },
    {
      id: "data-driven-creative-decisions",
      title: "Making data-driven creative decisions without killing creativity",
      excerpt: "Data should inform creativity, not constrain it. Learn how to use analytics to enhance creative decisions while preserving the spark of innovation.",
      date: "March 3, 2025",
      readTime: "10 min read",
      category: "Creative Strategy",
      image: "https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "data-driven-creative-decisions-balance"
    },
    {
      id: "mobile-first-marketing-2025",
      title: "Mobile-first marketing strategies for 2025",
      excerpt: "Mobile isn't the future—it's the present. Develop mobile-first marketing strategies that engage users in micro-moments and drive real results.",
      date: "March 5, 2025",
      readTime: "8 min read",
      category: "Mobile Marketing",
      image: "https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "mobile-first-marketing-strategies-2025"
    },
    {
      id: "traditional-funnels-dying",
      title: "Why traditional funnels are dying",
      excerpt: "The linear funnel is a relic of simpler times. Modern customer journeys are messy, non-linear, and require entirely new approaches to mapping and optimization.",
      date: "March 8, 2025",
      readTime: "9 min read",
      category: "Funnel Strategy",
      image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "why-traditional-funnels-dying"
    },
    {
      id: "ai-vs-human-landing-pages",
      title: "AI vs. human: who writes better landing pages?",
      excerpt: "A comprehensive analysis of AI-generated vs human-written landing pages. See real test results and learn when to use each approach for maximum conversions.",
      date: "March 10, 2025",
      readTime: "11 min read",
      category: "Conversion Optimization",
      image: "https://images.unsplash.com/photo-1563986768609-322da13575f3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "ai-vs-human-landing-pages"
    },
    {
      id: "ai-content-outperform-human-2026",
      title: "How AI content will outperform human creators by 2026",
      excerpt: "Discover the data-driven reasons why AI content creation is set to surpass human creators in efficiency, consistency, and results by 2026.",
      date: "March 12, 2025",
      readTime: "12 min read",
      category: "AI Content",
      image: "https://images.unsplash.com/photo-1677442136019-21780ecad995?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "how-ai-content-outperform-human-2026"
    },
    {
      id: "scale-lead-gen-without-team",
      title: "How to scale lead gen without scaling your team",
      excerpt: "Growth doesn't have to mean hiring sprees. Learn how to 10x your lead generation using automation, AI, and strategic systems instead of adding headcount.",
      date: "March 15, 2025",
      readTime: "10 min read",
      category: "Lead Generation",
      image: "https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "scale-lead-gen-without-scaling-team"
    },
    {
      id: "competitors-ai-strategies",
      title: "3 things your competitors already do with AI (and you don't)",
      excerpt: "While you're debating AI adoption, your competitors are already winning with it. Discover the 3 AI strategies they're using to gain unfair advantages.",
      date: "March 18, 2025",
      readTime: "8 min read",
      category: "Competitive Intelligence",
      image: "https://images.unsplash.com/photo-1518186285589-2f7649de83e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "3-things-competitors-already-do-ai"
    },
    {
      id: "future-marketing-automation",
      title: "The future of marketing automation (with real use cases)",
      excerpt: "Discover the evolution of marketing automation with real-world use cases and predictions for the future of automated marketing strategies.",
      date: "March 20, 2025",
      readTime: "11 min read",
      category: "Marketing Automation",
      image: "https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "future-marketing-automation-real-use-cases"
    },
    {
      id: "omnichannel-growth-engine",
      title: "Building an omnichannel growth engine in 60 days",
      excerpt: "Stop playing channel whack-a-mole. Learn how to build a cohesive omnichannel strategy that amplifies results across all touchpoints in just 60 days.",
      date: "March 22, 2025",
      readTime: "13 min read",
      category: "Growth Strategy",
      image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "omnichannel-growth-engine-60-days"
    },
    {
      id: "b2b-marketing-ux-makeover",
      title: "B2B marketing UX makeover with AI",
      excerpt: "B2B marketing experiences are notoriously poor. Discover how AI can transform your B2B marketing UX to engage and convert modern buyers.",
      date: "March 25, 2025",
      readTime: "9 min read",
      category: "B2B Marketing",
      image: "https://images.unsplash.com/photo-1559136555-9303baea8ebd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "b2b-marketing-ux-makeover-ai"
    },
    {
      id: "influencer-marketing-authenticity-2025",
      title: "Influencer marketing in 2025: authenticity over reach",
      excerpt: "The influencer marketing landscape has evolved. Learn why micro-influencers with authentic engagement often outperform mega-influencers.",
      date: "March 28, 2025",
      readTime: "9 min read",
      category: "Influencer Marketing",
      image: "https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "influencer-marketing-authenticity-2025"
    },
    {
      id: "conversion-optimization-psychology",
      title: "The psychology behind high-converting landing pages",
      excerpt: "Great landing pages tap into psychological triggers that drive action. Learn the cognitive biases and psychological principles that boost conversions.",
      date: "March 30, 2025",
      readTime: "10 min read",
      category: "Conversion Psychology",
      image: "https://images.unsplash.com/photo-1559136555-9303baea8ebd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "conversion-optimization-psychology-landing-pages"
    },
    {
      id: "video-marketing-dominance-2025",
      title: "Why video marketing will dominate 2025 (and how to get started)",
      excerpt: "Video content is taking over every platform. Learn why video marketing is essential for 2025 and get a practical roadmap for getting started.",
      date: "April 1, 2025",
      readTime: "8 min read",
      category: "Video Marketing",
      image: "https://images.unsplash.com/photo-1605810230434-7631ac76ec81?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "video-marketing-dominance-2025"
    },
    {
      id: "conversion-rate-optimization-audit",
      title: "Complete CRO audit checklist: find hidden conversion killers",
      excerpt: "Most websites have invisible conversion barriers killing 30-50% of potential sales. Use this comprehensive audit checklist to uncover and fix what's costing you money.",
      date: "April 3, 2025",
      readTime: "15 min read",
      category: "Conversion Optimization",
      image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "conversion-rate-optimization-audit-checklist"
    },
    {
      id: "lead-magnet-strategies-2025",
      title: "Lead magnet strategies that convert 40%+ of visitors",
      excerpt: "Generic ebooks are dead. Discover the high-converting lead magnets that turn cold traffic into qualified prospects and paying customers.",
      date: "April 5, 2025",
      readTime: "12 min read",
      category: "Lead Generation",
      image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "lead-magnet-strategies-high-conversion"
    },
    {
      id: "google-ads-optimization-2025",
      title: "Google Ads optimization: from wasteful spending to profitable ROI",
      excerpt: "Stop burning money on Google Ads. Learn the data-driven optimization techniques that turn unprofitable campaigns into consistent revenue generators.",
      date: "April 8, 2025",
      readTime: "14 min read",
      category: "Paid Advertising",
      image: "https://images.unsplash.com/photo-1553729459-efe14ef6055d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "google-ads-optimization-profitable-roi"
    },
    {
      id: "pricing-psychology-premium-brands",
      title: "Pricing psychology for premium brands: charge what you're worth",
      excerpt: "Premium brands that compete on price lose their positioning. Master the psychological pricing strategies that allow you to charge premium rates and increase profits.",
      date: "April 10, 2025",
      readTime: "11 min read",
      category: "Pricing Strategy",
      image: "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "pricing-psychology-premium-brands"
    },
    {
      id: "customer-lifetime-value-optimization",
      title: "CLV optimization: turn one-time buyers into lifetime customers",
      excerpt: "Acquiring customers is expensive—maximizing their lifetime value is profitable. Learn proven strategies to increase customer lifetime value by 200%+.",
      date: "April 12, 2025",
      readTime: "13 min read",
      category: "Customer Retention",
      image: "https://images.unsplash.com/photo-1559136555-9303baea8ebd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "customer-lifetime-value-optimization"
    },
    {
      id: "local-seo-netherlands-businesses",
      title: "Local SEO for Netherlands businesses: dominate your market",
      excerpt: "Dutch businesses need location-specific SEO strategies. Learn how to dominate local search results and capture high-intent customers in your area.",
      date: "April 15, 2025",
      readTime: "10 min read",
      category: "Local SEO",
      image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "local-seo-netherlands-businesses"
    },
    {
      id: "sales-funnel-psychology",
      title: "Sales funnel psychology: what makes people buy (and what doesn't)",
      excerpt: "Understanding buyer psychology is the difference between funnels that convert and funnels that leak. Learn the psychological triggers that drive purchase decisions.",
      date: "April 18, 2025",
      readTime: "12 min read",
      category: "Sales Psychology",
      image: "https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "sales-funnel-psychology-buying-decisions"
    },
    {
      id: "brand-positioning-competitive-advantage",
      title: "Brand positioning that creates unbeatable competitive advantage",
      excerpt: "In crowded markets, positioning determines profitability. Learn how to position your brand so uniquely that competition becomes irrelevant.",
      date: "April 20, 2025",
      readTime: "11 min read",
      category: "Brand Positioning",
      image: "https://images.unsplash.com/photo-1611224923853-80b023f02d71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "brand-positioning-competitive-advantage"
    },
    {
      id: "growth-hacking-b2b-saas",
      title: "Growth hacking for B2B SaaS: from startup to scale-up",
      excerpt: "B2B SaaS growth requires different tactics than B2C. Discover the growth hacking strategies that help SaaS companies achieve exponential growth.",
      date: "April 22, 2025",
      readTime: "14 min read",
      category: "Growth Hacking",
      image: "https://images.unsplash.com/photo-1518186285589-2f7649de83e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "growth-hacking-b2b-saas-strategies"
    },
    {
      id: "marketing-attribution-modeling",
      title: "Marketing attribution modeling: know what's really driving sales",
      excerpt: "Last-click attribution is killing your marketing decisions. Learn advanced attribution modeling to understand the true customer journey and optimize spend.",
      date: "April 25, 2025",
      readTime: "13 min read",
      category: "Marketing Analytics",
      image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      slug: "marketing-attribution-modeling-guide"
    }
  ];

  return (
    <div className="min-h-screen">
      <Navbar />
      
      {/* Hero Section */}
      <section className="pt-24 pb-16 bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <div className="container mx-auto px-4 md:px-6">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="text-center max-w-4xl mx-auto"
          >
<h1 className="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent py-[35px]">
  Growth Marketing Insights
  <br />
</h1>

            <p className="text-xl md:text-2xl text-muted-foreground mb-8 leading-relaxed">
              Data-driven strategies and AI-powered insights to help Dutch businesses 
              scale smarter, faster, and more profitably
            </p>
            <div className="flex flex-col items-center gap-4 justify-center mb-12">
              <p className="text-lg font-medium">Join 700+ Members getting growth insights</p>
              <form onSubmit={handleNewsletterSubmit} className="flex gap-2 max-w-md w-full">
                <Input 
                  type="email"
                  placeholder="Enter your email" 
                  className="bg-white/80 backdrop-blur-sm" 
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
                <Button 
                  type="submit"
                  disabled={isSubmitting}
                  size="lg"
                  className="bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700 shrink-0"
                >
                  {isSubmitting ? "..." : "Join"}
                  {!isSubmitting && <ArrowRight className="ml-2 h-5 w-5" />}
                </Button>
              </form>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Blog Posts Grid */}
      <section className="py-16">
        <div className="container mx-auto px-4 md:px-6">
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {blogPosts.map((post, index) => (
              <motion.div
                key={post.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
              >
                <Link to={`/blog/${post.slug}`}>
                  <Card className="h-full group hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                    <div className="aspect-video overflow-hidden rounded-t-lg">
                      <img 
                        src={post.image}
                        alt={post.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                    </div>
                    <CardContent className="p-6">
                      <div className="flex items-center gap-4 mb-4">
                        <Badge variant="secondary" className="bg-gradient-to-r from-booming-500 to-venture-500 text-white">
                          {post.category}
                        </Badge>
                        <div className="flex items-center text-sm text-muted-foreground">
                          <CalendarDays className="h-4 w-4 mr-1" />
                          {post.date}
                        </div>
                      </div>
                      
                      <h3 className="text-xl font-bold mb-3 group-hover:text-booming-600 transition-colors line-clamp-2">
                        {post.title}
                      </h3>
                      
                      <p className="text-muted-foreground mb-4 line-clamp-3">
                        {post.excerpt}
                      </p>
                      
                      <div className="flex items-center justify-between">
                        <div className="flex items-center text-sm text-muted-foreground">
                          <Clock className="h-4 w-4 mr-1" />
                          {post.readTime}
                        </div>
                        <ArrowRight className="h-4 w-4 text-booming-600 group-hover:translate-x-1 transition-transform" />
                      </div>
                    </CardContent>
                  </Card>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Newsletter CTA */}
      <section className="py-16 bg-gradient-to-r from-booming-600 to-venture-600">
        <div className="container mx-auto px-4 md:px-6 text-center">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="max-w-2xl mx-auto text-white"
          >
            <h2 className="text-3xl md:text-4xl font-bold mb-6">
              Stay Ahead of the Growth Curve
            </h2>
            <p className="text-xl mb-8 text-blue-100">
              Join 700+ Dutch business leaders getting weekly insights on AI marketing, 
              growth strategies, and conversion optimization.
            </p>
            <form onSubmit={handleBottomNewsletterSubmit} className="flex gap-2 max-w-md mx-auto mb-6">
              <Input 
                type="email"
                placeholder="Enter your email" 
                className="bg-white/90 text-gray-900 placeholder:text-gray-500" 
                value={bottomEmail}
                onChange={(e) => setBottomEmail(e.target.value)}
                required
              />
              <Button 
                type="submit"
                disabled={bottomIsSubmitting}
                size="lg"
                className="bg-white text-booming-600 hover:bg-gray-100 shrink-0"
              >
                {bottomIsSubmitting ? "..." : "Subscribe"}
                {!bottomIsSubmitting && <ArrowRight className="ml-2 h-5 w-5" />}
              </Button>
            </form>
            
            <div className="mt-6">
              <Button
                variant="outline"
                className="text-white border-white hover:bg-white hover:text-booming-600"
                onClick={() => window.location.href = 'mailto:info@boomingventure.com'}
              >
                Email Us Directly
              </Button>
            </div>
          </motion.div>
        </div>
      </section>
      
      <Footer />
    </div>
  );
};

export default Blog;
