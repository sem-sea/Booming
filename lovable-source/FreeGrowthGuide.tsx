
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Download, CheckCircle, TrendingUp, Target, Zap, Shield } from "lucide-react";
import { useState } from "react";
import { toast } from "@/hooks/use-toast";
import { motion } from "framer-motion";
import { addContactToBrevo } from "@/services/brevoService";
import { downloadGrowthGuidePDF } from "@/components/growth-guide/GrowthGuidePDF";
import { checkForSpam, checkRateLimit } from "@/utils/spamFilter";
import Captcha from "@/components/security/Captcha";

const FreeGrowthGuide = () => {
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [captchaToken, setCaptchaToken] = useState<string | null>(null);
  const [showCaptcha, setShowCaptcha] = useState(false);

  const handleCaptchaVerify = (token: string | null) => {
    setCaptchaToken(token);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
      toast({
        title: "Please enter a valid email",
        description: "We need your email to provide you with the growth strategy guide",
        variant: "destructive",
      });
      return;
    }

    // Rate limiting check
    const clientIP = 'growth_guide_session';
    if (checkRateLimit(clientIP, 2, 300000)) { // 2 requests per 5 minutes
      toast({
        title: "Too many requests",
        description: "Please wait a few minutes before requesting again.",
        variant: "destructive",
      });
      return;
    }

    // Enhanced spam filtering
    const spamCheck = checkForSpam(email);
    
    // If medium spam confidence, require CAPTCHA
    if (spamCheck.confidence > 25 && !showCaptcha) {
      setShowCaptcha(true);
      toast({
        title: "Security verification required",
        description: "Please complete the security verification below.",
        variant: "default",
      });
      return;
    }
    
    if (spamCheck.isSpam) {
      console.log('Spam detected in growth guide:', spamCheck);
      toast({
        title: "Invalid email",
        description: "Please enter a valid business email address.",
        variant: "destructive",
      });
      return;
    }

    // CAPTCHA validation for suspicious content
    if (showCaptcha && !captchaToken) {
      toast({
        title: "Security verification required",
        description: "Please complete the CAPTCHA verification.",
        variant: "destructive",
      });
      return;
    }

    setIsSubmitting(true);
    
    try {
      const success = await addContactToBrevo({
        email,
        attributes: {
          LEAD_SOURCE: "Free Growth Guide Section",
          SPAM_SCORE: spamCheck.confidence.toString(),
          CAPTCHA_VERIFIED: showCaptcha ? 'true' : 'false'
        },
        listIds: [2] // Free Strategy Guide list
      });

      if (success) {
        // Generate and download PDF immediately
        downloadGrowthGuidePDF();
        
        toast({
          title: "Thank you!",
          description: "Your Free Growth Strategy Guide is now downloading. Check your downloads folder.",
          variant: "default",
        });
        setEmail("");
        setCaptchaToken(null);
        setShowCaptcha(false);
      } else {
        throw new Error("Failed to add contact");
      }
    } catch (error) {
      console.error("Error submitting form:", error);
      toast({
        title: "Something went wrong",
        description: "Please try again or contact support.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <section className="py-16 md:py-24 bg-gradient-to-r from-booming-50 to-venture-50">
      <div className="container mx-auto px-4 md:px-6">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7 }}
          >
            <div className="relative">
              <img 
                src="https://images.unsplash.com/photo-1553484771-371a605b060b?q=80&w=2070&auto=format&fit=crop" 
                alt="Growth strategy guide mockup"
                className="w-full max-w-md mx-auto rounded-lg shadow-lg"
              />
              <div className="absolute -bottom-4 -right-4 bg-booming-600 text-white p-3 rounded-full">
                <Download className="h-6 w-6" />
              </div>
            </div>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7, delay: 0.2 }}
            className="space-y-6"
          >
            <div>
              <h2 className="text-3xl md:text-4xl font-bold mb-4">
                Get Your Free Growth Strategy Guide
              </h2>
              <p className="text-lg text-foreground/70 mb-6">
                Discover the exact framework our clients use to achieve consistent 
                48%+ growth. This comprehensive guide includes actionable strategies, 
                templates, and real case studies.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
              <div className="flex items-start space-x-3">
                <div className="bg-booming-100 p-2 rounded-full">
                  <TrendingUp className="h-5 w-5 text-booming-600" />
                </div>
                <div>
                  <h4 className="font-semibold">Growth Frameworks</h4>
                  <p className="text-sm text-foreground/70">Proven methodologies for sustainable growth</p>
                </div>
              </div>
              
              <div className="flex items-start space-x-3">
                <div className="bg-venture-100 p-2 rounded-full">
                  <Target className="h-5 w-5 text-venture-600" />
                </div>
                <div>
                  <h4 className="font-semibold">ROI Templates</h4>
                  <p className="text-sm text-foreground/70">Calculate and track your marketing ROI</p>
                </div>
              </div>
              
              <div className="flex items-start space-x-3">
                <div className="bg-booming-100 p-2 rounded-full">
                  <Zap className="h-5 w-5 text-booming-600" />
                </div>
                <div>
                  <h4 className="font-semibold">AI Implementation</h4>
                  <p className="text-sm text-foreground/70">Step-by-step AI integration guide</p>
                </div>
              </div>
              
              <div className="flex items-start space-x-3">
                <div className="bg-venture-100 p-2 rounded-full">
                  <CheckCircle className="h-5 w-5 text-venture-600" />
                </div>
                <div>
                  <h4 className="font-semibold">Case Studies</h4>
                  <p className="text-sm text-foreground/70">Real examples from successful clients</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-sm">
              <div className="flex flex-col sm:flex-row gap-3">
                <Input
                  type="email"
                  placeholder="Enter your business email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="flex-grow"
                  required
                />
                <Button 
                  type="submit" 
                  disabled={isSubmitting || (showCaptcha && !captchaToken)}
                  className="bg-booming-600 hover:bg-booming-700 text-white px-8"
                >
                  {isSubmitting ? "Preparing..." : "Download Free Guide"}
                  <Download className="ml-2 h-4 w-4" />
                </Button>
              </div>

              {showCaptcha && (
                <div className="mt-4">
                  <Captcha 
                    onVerify={handleCaptchaVerify}
                    size="compact"
                    theme="light"
                  />
                </div>
              )}

              <div className="flex items-center justify-between mt-2">
                <p className="text-xs text-foreground/60">
                  Your guide will download immediately. No spam, ever.
                </p>
                <div className="flex items-center gap-1 text-xs text-foreground/60">
                  <Shield className="h-3 w-3" />
                  <span>Secured</span>
                </div>
              </div>
              
              <div className="mt-4">
                <Button
                  type="button"
                  variant="outline"
                  className="w-full text-black border-black hover:bg-black hover:text-white"
                  onClick={() => window.location.href = 'mailto:info@boomingventure.com'}
                >
                  Email Us Directly
                </Button>
              </div>
            </form>
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default FreeGrowthGuide;
