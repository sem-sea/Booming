
import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, TrendingDown, TrendingUp, Zap } from "lucide-react";

const WhyTraditionalFunnelsDying = () => {
  useEffect(() => {
    document.title = "Why Traditional Funnels Are Dying — and What's Replacing Them | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "Why Traditional Funnels Are Dying — and What's Replacing Them",
      "description": "Traditional linear funnels no longer match customer behavior. Discover the new models that align with modern buyer journeys and drive better results.",
      "author": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Booming Venture",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Rotterdam",
          "addressCountry": "NL"
        }
      },
      "datePublished": "2024-02-10",
      "dateModified": "2024-02-10",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://boomingventure.com/blog/why-traditional-funnels-dying"
      }
    };
    
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.text = JSON.stringify(structuredData);
    document.head.appendChild(script);
    
    return () => {
      document.head.removeChild(script);
    };
  }, []);

  return (
    <div className="min-h-screen">
      <Navbar />
      
      <article className="pt-24 pb-16">
        <div className="container mx-auto px-4 md:px-6 max-w-4xl">
          <Link to="/blog" className="inline-flex items-center text-booming-600 hover:text-booming-700 mb-8 group">
            <ArrowLeft className="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform" />
            Back to Blog
          </Link>
          
          <motion.header
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="mb-12"
          >
            <div className="flex items-center gap-4 mb-6">
              <Badge variant="secondary" className="bg-gradient-to-r from-booming-500 to-venture-500 text-white">
                Funnel Strategy
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                February 10, 2024
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                9 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              Why Traditional Funnels Are Dying — and What's Replacing Them
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Traditional linear funnels no longer match customer behavior. Discover the new models that align with modern buyer journeys and drive better results.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Evolution of marketing funnels"
              className="w-full h-[400px] object-cover rounded-lg shadow-lg"
            />
          </motion.div>
          
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="prose prose-lg max-w-none"
          >
            <p className="text-lg leading-relaxed mb-8">
              The traditional marketing funnel—a linear progression from awareness to purchase—worked beautifully in an era of limited information and fewer touchpoints. But today's customer journey is anything but linear. Modern buyers research across multiple channels, comparison shop extensively, and often circle back through different stages multiple times before making a decision.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Death of Linear Thinking</h2>
            
            <div className="grid md:grid-cols-2 gap-6 my-8">
              <Card className="border-t-4 border-t-red-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <TrendingDown className="h-6 w-6 text-red-600 mr-2" />
                    <h4 className="text-xl font-bold text-red-700">Traditional Funnel Problems</h4>
                  </div>
                  <ul className="space-y-2 text-gray-700">
                    <li>• Assumes linear customer progression</li>
                    <li>• Ignores multiple touchpoints</li>
                    <li>• Focuses on single-channel attribution</li>
                    <li>• Can't handle complex B2B buying committees</li>
                    <li>• Misses the influence of peer reviews and social proof</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-green-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <TrendingUp className="h-6 w-6 text-green-600 mr-2" />
                    <h4 className="text-xl font-bold text-green-700">Modern Customer Reality</h4>
                  </div>
                  <ul className="space-y-2 text-gray-700">
                    <li>• Non-linear journey with loops and jumps</li>
                    <li>• Multiple research phases</li>
                    <li>• Cross-channel behavior</li>
                    <li>• Peer influence and social validation</li>
                    <li>• Extended consideration periods</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "Companies using modern funnel approaches see 23% higher conversion rates and 18% shorter sales cycles compared to traditional linear models." - Marketing Technology Research, 2024
            </blockquote>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">What's Replacing Traditional Funnels</h2>
            
            <h3 className="text-2xl font-semibold mb-4">1. The Customer Journey Map</h3>
            
            <p className="mb-6">
              Instead of a funnel, think of a journey map that acknowledges multiple entry points, various paths, and feedback loops. Modern customers might discover your brand through social media, research on your website, compare on review sites, return to your content, and finally convert through a completely different channel.
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">2. The Flywheel Model</h3>
            
            <p className="mb-6">
              HubSpot popularized the flywheel concept—a circular model where customers become promoters who attract new customers. This model emphasizes the ongoing relationship beyond the initial purchase, recognizing that customer success drives sustainable growth.
            </p>
            
            <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-blue-800">The Three Stages of the Flywheel:</h4>
              <div className="grid md:grid-cols-3 gap-4">
                <div>
                  <strong className="text-blue-700">Attract</strong>
                  <p className="text-sm text-gray-600 mt-2">Draw in the right audience with valuable content and experiences</p>
                </div>
                <div>
                  <strong className="text-blue-700">Engage</strong>
                  <p className="text-sm text-gray-600 mt-2">Build relationships and provide solutions to their problems</p>
                </div>
                <div>
                  <strong className="text-blue-700">Delight</strong>
                  <p className="text-sm text-gray-600 mt-2">Exceed expectations to create promoters and advocates</p>
                </div>
              </div>
            </div>
            
            <h3 className="text-2xl font-semibold mb-4">3. The Messy Middle Framework</h3>
            
            <p className="mb-6">
              Google's "Messy Middle" research reveals that between initial trigger and purchase lies a complex web of exploration and evaluation. Customers loop between these phases, influenced by cognitive biases and behavioral triggers.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Implementing Modern Funnel Thinking</h2>
            
            <h3 className="text-2xl font-semibold mb-4">Multi-Touch Attribution</h3>
            
            <p className="mb-6">
              Modern attribution models recognize that conversion is rarely the result of a single touchpoint. Implement attribution that gives credit across the entire customer journey, not just the last click.
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">Content for Every Micro-Moment</h3>
            
            <p className="mb-6">
              Create content that serves customers at every stage of their non-linear journey. This means having educational content, comparison tools, social proof, and conversion-focused materials all working together.
            </p>
            
            <div className="grid md:grid-cols-2 gap-6 my-8">
              <div className="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-6">
                <h4 className="text-xl font-bold mb-4 text-purple-800">Traditional Metrics to Replace</h4>
                <ul className="space-y-2 text-purple-700">
                  <li>• Single-source attribution</li>
                  <li>• Linear conversion paths</li>
                  <li>• Channel-specific ROI only</li>
                  <li>• First-touch or last-touch attribution</li>
                </ul>
              </div>
              
              <div className="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6">
                <h4 className="text-xl font-bold mb-4 text-green-800">Modern Metrics to Track</h4>
                <ul className="space-y-2 text-green-700">
                  <li>• Multi-touch attribution</li>
                  <li>• Customer lifetime value</li>
                  <li>• Net Promoter Score</li>
                  <li>• Customer effort score</li>
                </ul>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Future is Adaptive</h2>
            
            <p className="mb-6">
              The most successful companies are those that embrace the complexity of modern customer behavior. They build systems that adapt to individual customer journeys rather than forcing customers into predetermined paths.
            </p>
            
            <p className="text-lg font-medium mb-8">
              The funnel isn't dead—it's evolved. And companies that evolve with it will capture the opportunities that rigid thinkers miss.
            </p>
          </motion.div>
          
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16"
          >
            <Card className="bg-gradient-to-r from-booming-600 to-venture-600 text-white border-none">
              <CardContent className="p-8 text-center">
                <h3 className="text-2xl font-bold mb-4">Ready to Modernize Your Funnel?</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Discover where your traditional funnel is leaking revenue and get a modern optimization strategy
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
                      <Zap className="h-5 w-5 mr-2" />
                      Analyze My Funnel
                    </Button>
                  </Link>
                  <Link to="/#contact">
                    <Button variant="outline" className="border-white text-black bg-white hover:bg-gray-100 px-8 py-3">
                      Get Expert Help
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default WhyTraditionalFunnelsDying;
