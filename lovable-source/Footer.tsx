
import { ArrowRight, Linkedin } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useState } from "react";
import { toast } from "@/hooks/use-toast";
import { addContactToBrevo } from "@/services/brevoService";

const Footer = () => {
  const year = new Date().getFullYear();
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const footerLinks = [
    {
      title: "Company",
      links: [
        { label: "About Us", href: "/about" },
        { label: "UNIFY Framework™", href: "/unify-framework" },
        { label: "Our Team", href: "/about" },
        { label: "Services", href: "/services" },
        { label: "Contact", href: "#contact" },
      ],
    },
    {
      title: "Services",
      links: [
        { label: "Strategic Consulting", href: "/services" },
        { label: "Performance Marketing", href: "/services" },
        { label: "AI Solutions", href: "/services" },
        { label: "Growth Optimization", href: "/services" },
      ],
    },
    {
      title: "Tools",
      links: [
        { label: "Funnel Leak Calculator", href: "/funnel-calculator" },
        { label: "ROI Forecaster", href: "/roi-forecaster" },
        { label: "Free Consultation", href: "#contact" },
        { label: "Case Studies", href: "#contact" },
      ],
    },
  ];

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

    setIsSubmitting(true);
    
    try {
      const success = await addContactToBrevo({
        email,
        attributes: {
          LEAD_SOURCE: "Newsletter Footer"
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

  return (
    <footer className="bg-white border-t">
      <div className="container mx-auto px-4 md:px-6 py-12 md:py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
          {/* Company Info */}
          <div className="lg:col-span-2">
            <h3 className="text-2xl font-bold gradient-text mb-4">
              Booming<span className="text-venture-600">Venture</span>
            </h3>
            <p className="text-foreground/70 mb-4 max-w-md">
              Based from Rotterdam, The Netherlands, we help businesses grow through innovative marketing strategies and AI-driven solutions.
            </p>
            
            <div className="mb-6 text-sm text-foreground/60">
              <p>Breedveldsingel 1</p>
              <p>3055 PG Rotterdam</p>
              <p>The Netherlands</p>
              <p>Email: info@boomingventure.com</p>
            </div>
            
            {/* Social Media */}
            <div className="mb-6">
              <a 
                href="https://www.linkedin.com/company/booming-venture/about/"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center justify-center w-10 h-10 bg-booming-600 hover:bg-booming-700 text-white rounded-full transition-colors"
              >
                <Linkedin size={20} />
              </a>
            </div>
            
            <div className="space-y-2">
              <p className="text-sm font-medium">Join 700+ Members getting growth insights</p>
              <form onSubmit={handleNewsletterSubmit} className="flex gap-2 max-w-sm">
                <Input 
                  type="email"
                  placeholder="Enter email" 
                  className="bg-booming-50/30 text-sm" 
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
                <Button 
                  type="submit"
                  disabled={isSubmitting}
                  size="sm"
                  className="bg-booming-600 hover:bg-booming-700 shrink-0"
                >
                  {isSubmitting ? "..." : "Join"}
                </Button>
              </form>
            </div>
          </div>
          
          {/* Footer Links */}
          {footerLinks.map((column) => (
            <div key={column.title}>
              <h4 className="font-semibold text-lg mb-4">{column.title}</h4>
              <ul className="space-y-3">
                {column.links.map((link) => (
                  <li key={link.label}>
                    <a 
                      href={link.href} 
                      className="text-foreground/70 hover:text-booming-600 transition-colors"
                    >
                      {link.label}
                    </a>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
        
        <div className="border-t mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p className="text-foreground/60 text-sm mb-4 md:mb-0">
            © {year} Booming Venture. All rights reserved.
          </p>
          
          <div className="flex space-x-6">
            <a 
              href="/privacy-policy" 
              className="text-foreground/60 hover:text-booming-600 text-sm transition-colors"
            >
              Privacy Policy
            </a>
            <a 
              href="/disclaimer" 
              className="text-foreground/60 hover:text-booming-600 text-sm transition-colors"
            >
              Disclaimer
            </a>
            <a 
              href="/terms-of-service" 
              className="text-foreground/60 hover:text-booming-600 text-sm transition-colors"
            >
              Terms of Service
            </a>
            <a 
              href="#contact" 
              className="text-foreground/60 hover:text-booming-600 text-sm transition-colors"
            >
              Contact Us
            </a>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
