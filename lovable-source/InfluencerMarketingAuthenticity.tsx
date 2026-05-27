import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, Users, Heart, TrendingUp, Target } from "lucide-react";

const InfluencerMarketingAuthenticity = () => {
  useEffect(() => {
    document.title = "Influencer Marketing in 2025: Authenticity Over Reach | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "Influencer Marketing in 2025: Authenticity Over Reach",
      "description": "The influencer marketing landscape has evolved. Learn why micro-influencers with authentic engagement often outperform mega-influencers.",
      "author": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "datePublished": "2025-02-03",
      "dateModified": "2025-02-03"
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
                Influencer Marketing
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                February 3, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                9 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              Influencer Marketing in 2025: Authenticity Over Reach
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              The influencer marketing landscape has evolved. Learn why micro-influencers with authentic engagement often outperform mega-influencers.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Influencer marketing authenticity - social media content creation"
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
              The days of throwing money at mega-influencers with millions of followers are fading. In 2025, smart brands are discovering that authentic connections trump massive reach—and the data proves it.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Authenticity Revolution</h2>
            
            <p className="mb-6">
              Recent studies show that micro-influencers (1K-100K followers) generate 60% higher engagement rates than macro-influencers. But it's not just about the numbers—it's about the quality of connection.
            </p>
            
            <div className="grid md:grid-cols-2 gap-6 my-8">
              <Card className="border-t-4 border-t-blue-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Heart className="h-6 w-6 text-red-500 mr-2" />
                    <h4 className="text-xl font-bold text-blue-700">Micro-Influencers</h4>
                  </div>
                  <ul className="space-y-2 text-gray-700">
                    <li>• 6.7% average engagement rate</li>
                    <li>• 22.2% higher conversion rates</li>
                    <li>• 6.7x more cost-effective</li>
                    <li>• Higher audience trust</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-orange-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Users className="h-6 w-6 text-orange-600 mr-2" />
                    <h4 className="text-xl font-bold text-orange-700">Mega-Influencers</h4>
                  </div>
                  <ul className="space-y-2 text-gray-700">
                    <li>• 1.7% average engagement rate</li>
                    <li>• Higher reach but lower impact</li>
                    <li>• Premium pricing</li>
                    <li>• Often seen as "advertisements"</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Why Authenticity Wins</h2>
            
            <h3 className="text-2xl font-semibold mb-4">1. Trust Factor</h3>
            <p className="mb-6">
              Micro-influencers maintain personal relationships with their audience. When they recommend a product, it feels like advice from a friend, not a paid advertisement.
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">2. Niche Expertise</h3>
            <p className="mb-6">
              Smaller influencers often focus on specific niches, giving them deep credibility in their area of expertise. A fitness micro-influencer's supplement recommendation carries more weight than a general lifestyle influencer's.
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">3. Higher Engagement Quality</h3>
            <p className="mb-6">
              Comments on micro-influencer posts are more likely to be genuine interactions rather than bot activity or superficial responses.
            </p>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "We shifted 80% of our influencer budget from mega to micro-influencers and saw a 340% increase in qualified leads within 3 months." - Marketing Director, Premium Skincare Brand
            </blockquote>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Building an Authentic Influencer Strategy</h2>
            
            <ol className="list-decimal pl-6 mb-8 space-y-4">
              <li>
                <strong>Define Your Niche</strong>
                <p className="text-gray-600 mt-2">Identify specific communities that align with your brand values and target audience.</p>
              </li>
              <li>
                <strong>Evaluate Engagement Quality</strong>
                <p className="text-gray-600 mt-2">Look beyond follower count to analyze comment quality, audience demographics, and engagement patterns.</p>
              </li>
              <li>
                <strong>Build Long-term Relationships</strong>
                <p className="text-gray-600 mt-2">Focus on ongoing partnerships rather than one-off posts for better authenticity and results.</p>
              </li>
              <li>
                <strong>Allow Creative Freedom</strong>
                <p className="text-gray-600 mt-2">Trust influencers to present your brand in their authentic voice rather than prescribing exact messaging.</p>
              </li>
              <li>
                <strong>Track Quality Metrics</strong>
                <p className="text-gray-600 mt-2">Measure engagement rate, click-through rate, and conversion rate—not just reach and impressions.</p>
              </li>
            </ol>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Red Flags to Avoid</h2>
            
            <ul className="list-disc pl-6 mb-8 space-y-2">
              <li>Sudden follower growth spikes (indicates bought followers)</li>
              <li>High follower count but low engagement rates</li>
              <li>Generic, repetitive comments</li>
              <li>Frequent sponsored content without authentic posts</li>
              <li>Audience demographics that don't match your target market</li>
            </ul>
            
            <p className="text-lg font-medium mb-8">
              The future of influencer marketing belongs to authentic voices that genuinely connect with their communities. Brands that prioritize trust over reach will see the highest returns in 2025 and beyond.
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
                <h3 className="text-2xl font-bold mb-4">Ready to Build Authentic Partnerships?</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Let's develop an influencer strategy that drives real results
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
                      <Target className="h-5 w-5 mr-2" />
                      Analyze Your Strategy
                    </Button>
                  </Link>
                  <Link to="/#contact">
                    <Button variant="outline" className="border-white text-black bg-white hover:bg-gray-100 px-8 py-3">
                      Get Expert Guidance
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

export default InfluencerMarketingAuthenticity;
