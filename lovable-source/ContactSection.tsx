
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Mail, MapPin, Send, Shield, Linkedin } from "lucide-react";
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { addContactToBrevo } from "@/services/brevoService";
import { checkForSpam, checkRateLimit } from "@/utils/spamFilter";
import Captcha from "@/components/security/Captcha";

const ContactSection = () => {
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    company: "",
    message: ""
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [captchaToken, setCaptchaToken] = useState<string | null>(null);
  const [showCaptcha, setShowCaptcha] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleCaptchaVerify = (token: string | null) => {
    setCaptchaToken(token);
  };

  const handleCaptchaError = () => {
    toast({
      title: "Security verification failed",
      description: "Please try again or contact support if the problem persists.",
      variant: "destructive",
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.email || !formData.name || !formData.message) {
      toast({
        title: "Missing Information",
        description: "Please fill in all required fields.",
        variant: "destructive",
      });
      return;
    }

    // Rate limiting check
    const clientIP = 'user_session'; // In production, you'd want to use actual IP
    if (checkRateLimit(clientIP, 3, 300000)) { // 3 requests per 5 minutes
      toast({
        title: "Too many requests",
        description: "Please wait a few minutes before submitting again.",
        variant: "destructive",
      });
      return;
    }

    // Enhanced spam filtering
    const spamCheck = checkForSpam(formData.email, formData.message, formData.name);
    
    // If high spam confidence, require CAPTCHA
    if (spamCheck.confidence > 30 && !showCaptcha) {
      setShowCaptcha(true);
      toast({
        title: "Security verification required",
        description: "Please complete the security verification below.",
        variant: "default",
      });
      return;
    }

    if (spamCheck.isSpam) {
      console.log('Spam detected:', spamCheck);
      toast({
        title: "Message blocked",
        description: "Your message appears to be spam and cannot be submitted.",
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

    // Log suspicious activity (confidence > 20 but not blocked)
    if (spamCheck.confidence > 20) {
      console.log('Suspicious activity detected:', spamCheck);
    }

    setIsSubmitting(true);
    
    try {
      // Extract first and last name
      const nameParts = formData.name.split(' ');
      const firstName = nameParts[0] || '';
      const lastName = nameParts.slice(1).join(' ') || '';

      // Add contact to Brevo with Contact list ID #4
      const brevoSuccess = await addContactToBrevo({
        email: formData.email,
        attributes: {
          FIRSTNAME: firstName,
          LASTNAME: lastName,
          COMPANY: formData.company,
          LEAD_SOURCE: 'Contact Form',
          MESSAGE: formData.message,
          SPAM_SCORE: spamCheck.confidence.toString(),
          CAPTCHA_VERIFIED: showCaptcha ? 'true' : 'false'
        },
        listIds: [4] // Contact list
      });

      if (brevoSuccess) {
        toast({
          title: "Message sent!",
          description: "Thank you for contacting us. We'll get back to you soon.",
          duration: 5000,
        });
        
        // Reset form
        setFormData({
          name: "",
          email: "",
          company: "",
          message: ""
        });
        setCaptchaToken(null);
        setShowCaptcha(false);
      } else {
        toast({
          title: "Something went wrong",
          description: "We received your message but couldn't add you to our mailing list. We'll still get back to you!",
        });
      }
    } catch (error) {
      console.error("Form submission error:", error);
      toast({
        title: "Error",
        description: "There was a problem submitting your form. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <section id="contact" className="py-16 md:py-24 bg-booming-50">
      <div className="container mx-auto px-4 md:px-6">
        <div className="text-center max-w-3xl mx-auto mb-16 animate-fade-in">
          <h2 className="mb-4">Get In Touch</h2>
          <p className="text-xl text-foreground/70">
            Ready to accelerate your business growth? Contact us today for a free consultation.
          </p>
        </div>
        
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-12">
          <div className="lg:col-span-2 animate-fade-in" style={{ animationDelay: "0.1s" }}>
            <div className="bg-white rounded-lg p-8 shadow-sm h-full">
              <h3 className="text-2xl font-bold mb-6">Contact Information</h3>
              
              <div className="space-y-6">
                <div className="flex items-start">
                  <Mail className="h-6 w-6 text-booming-600 mr-4 mt-1" />
                  <div>
                    <h4 className="text-lg font-medium">Email Us</h4>
                    <p className="text-foreground/70">info@boomingventure.com</p>
                  </div>
                </div>
                
                <div className="flex items-start">
                  <MapPin className="h-6 w-6 text-booming-600 mr-4 mt-1" />
                  <div>
                    <h4 className="text-lg font-medium">Visit Us</h4>
                    <p className="text-foreground/70">
                      Breedveldsingel 1<br />
                      3055 PG Rotterdam<br />
                      The Netherlands
                    </p>
                  </div>
                </div>
              </div>
              
              <div className="mt-8 pt-6 border-t">
                <h4 className="text-lg font-medium mb-4">Connect With Us</h4>
                <div className="flex space-x-4">
                  <a
                    href="https://www.linkedin.com/company/booming-venture/about/"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center justify-center w-10 h-10 bg-booming-600 hover:bg-booming-700 text-white rounded-full transition-colors"
                  >
                    <Linkedin size={20} />
                  </a>
                </div>
              </div>
              
              <div className="mt-8 pt-6 border-t">
                <Button
                  variant="outline"
                  className="w-full text-black border-black hover:bg-black hover:text-white"
                  onClick={() => window.location.href = 'mailto:info@boomingventure.com'}
                >
                  Email Us Directly
                </Button>
              </div>
              
              <div className="mt-8 pt-6 border-t">
                <div className="flex items-center gap-2 text-sm text-foreground/60">
                  <Shield className="h-4 w-4" />
                  <span>Protected by spam filtering and security verification</span>
                </div>
              </div>
            </div>
          </div>
          
          <div className="lg:col-span-3 animate-fade-in" style={{ animationDelay: "0.3s" }}>
            <div className="bg-white rounded-lg p-8 shadow-sm">
              <h3 className="text-2xl font-bold mb-6">Send Us a Message</h3>
              
              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label htmlFor="name" className="text-sm font-medium">
                      Your Name *
                    </label>
                    <Input
                      id="name"
                      name="name"
                      placeholder="John Doe"
                      value={formData.name}
                      onChange={handleChange}
                      required
                      className="bg-booming-50/50"
                    />
                  </div>
                  
                  <div className="space-y-2">
                    <label htmlFor="email" className="text-sm font-medium">
                      Email Address *
                    </label>
                    <Input
                      id="email"
                      name="email"
                      type="email"
                      placeholder="john@example.com"
                      value={formData.email}
                      onChange={handleChange}
                      required
                      className="bg-booming-50/50"
                    />
                  </div>
                </div>
                
                <div className="space-y-2">
                  <label htmlFor="company" className="text-sm font-medium">
                    Company Name
                  </label>
                  <Input
                    id="company"
                    name="company"
                    placeholder="Your Company Ltd."
                    value={formData.company}
                    onChange={handleChange}
                    className="bg-booming-50/50"
                  />
                </div>
                
                <div className="space-y-2">
                  <label htmlFor="message" className="text-sm font-medium">
                    Your Message *
                  </label>
                  <Textarea
                    id="message"
                    name="message"
                    placeholder="How can we help you?"
                    rows={5}
                    value={formData.message}
                    onChange={handleChange}
                    required
                    className="bg-booming-50/50"
                  />
                </div>

                {showCaptcha && (
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Security Verification</label>
                    <Captcha 
                      onVerify={handleCaptchaVerify}
                      onError={handleCaptchaError}
                      size="normal"
                      theme="light"
                    />
                  </div>
                )}
                
                <Button 
                  type="submit" 
                  disabled={isSubmitting || (showCaptcha && !captchaToken)}
                  className="w-full bg-booming-600 hover:bg-booming-700 text-white font-medium py-6 h-auto"
                >
                  {isSubmitting ? "Sending..." : "Send Message"}
                  <Send className="ml-2 h-5 w-5" />
                </Button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default ContactSection;
