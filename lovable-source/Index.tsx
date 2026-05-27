
import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import HeroSection from "@/components/sections/HeroSection";
import ServicesSection from "@/components/sections/ServicesSection";
import AboutSection from "@/components/sections/AboutSection";
import ContactSection from "@/components/sections/ContactSection";
import TestimonialsSection from "@/components/sections/TestimonialsSection";
import AwardsBanner from "@/components/sections/AwardsBanner";
import FreeGrowthGuide from "@/components/sections/FreeGrowthGuide";
import Lottie from "react-lottie-player";
import loadingLottie from "@/assets/loading-lottie.json";
import { MessageCircle, Calculator, TrendingUp } from "lucide-react";

// Extend Window interface for DataSpeak
declare global {
  interface Window {
    dataspeakChatConfiguration?: {
      interfaceId: string;
    };
  }
}

const PreLoader = () => {
  return (
    <motion.div
      className="fixed inset-0 bg-gradient-to-tr from-booming-900 to-venture-900 z-50 flex items-center justify-center"
      initial={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      transition={{ duration: 0.8 }}
    >
      <div className="text-center">
        <Lottie
          loop
          animationData={loadingLottie}
          play
          style={{ width: 200, height: 200 }}
        />
        <motion.h2 
          className="text-white font-bold mt-4 bg-gradient-to-r from-white to-venture-300 bg-clip-text text-transparent"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.5 }}
        >
          Booming Venture
        </motion.h2>
        <motion.p
          className="text-white/70 mt-2"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.8 }}
        >
          Performance. Personality. Powered by AI.
        </motion.p>
      </div>
    </motion.div>
  );
};

const SmoothScrollLink = ({ to, children }: { to: string, children: React.ReactNode }) => {
  const handleClick = (e: React.MouseEvent) => {
    e.preventDefault();
    const target = document.querySelector(to);
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth'
      });
    }
  };
  
  return (
    <a href={to} onClick={handleClick} className="cursor-pointer">
      {children}
    </a>
  );
};

const FloatingActionButton = () => {
  return (
    <div className="fixed bottom-8 right-8 z-40 flex flex-col gap-4">
      <motion.div
        initial={{ opacity: 0, scale: 0.8 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ delay: 2.9 }}
      >
        <Link to="/roi-forecaster">
          <motion.button
            className="bg-gradient-to-r from-venture-600 to-booming-600 p-4 rounded-full shadow-lg text-white flex items-center justify-center glow-on-hover"
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
          >
            <TrendingUp />
          </motion.button>
        </Link>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, scale: 0.8 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ delay: 2.7 }}
      >
        <Link to="/funnel-calculator">
          <motion.button
            className="bg-gradient-to-r from-venture-600 to-booming-600 p-4 rounded-full shadow-lg text-white flex items-center justify-center glow-on-hover"
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
          >
            <Calculator />
          </motion.button>
        </Link>
      </motion.div>
      
      <motion.div
        initial={{ opacity: 0, scale: 0.8 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ delay: 2.5 }}
      >
        <SmoothScrollLink to="#contact">
          <motion.button
            className="bg-gradient-to-r from-booming-600 to-venture-600 p-4 rounded-full shadow-lg text-white flex items-center justify-center glow-on-hover"
            whileHover={{ scale: 1.1 }}
            whileTap={{ scale: 0.9 }}
          >
            <MessageCircle />
          </motion.button>
        </SmoothScrollLink>
      </motion.div>
    </div>
  );
};

const ScrollProgress = () => {
  const [scrollProgress, setScrollProgress] = useState(0);
  
  useEffect(() => {
    const handleScroll = () => {
      const totalScroll = document.documentElement.scrollHeight - window.innerHeight;
      const currentScroll = window.scrollY;
      const progress = (currentScroll / totalScroll) * 100;
      setScrollProgress(progress);
    };
    
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);
  
  return (
    <motion.div
      className="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-booming-500 to-venture-500 z-50"
      style={{ width: `${scrollProgress}%` }}
    />
  );
};

const ParallaxBackgrounds = () => {
  const [scrollY, setScrollY] = useState(0);
  
  useEffect(() => {
    const handleScroll = () => setScrollY(window.scrollY);
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);
  
  return (
    <div className="fixed inset-0 pointer-events-none z-0">
      <div 
        className="absolute top-[10%] right-[10%] w-32 h-32 rounded-full bg-gradient-to-br from-booming-300/10 to-venture-400/10 blur-xl"
        style={{ transform: `translateY(${scrollY * 0.05}px)` }}
      />
      <div 
        className="absolute top-[40%] left-[5%] w-48 h-48 rounded-full bg-gradient-to-tr from-venture-300/10 to-booming-400/10 blur-xl"
        style={{ transform: `translateY(${scrollY * -0.08}px)` }}
      />
      <div 
        className="absolute bottom-[20%] right-[15%] w-40 h-40 rounded-full bg-gradient-to-bl from-booming-400/10 to-venture-300/10 blur-xl"
        style={{ transform: `translateY(${scrollY * -0.06}px)` }}
      />
      <div className="absolute inset-0 opacity-[0.03] pointer-events-none grid-bg" />
    </div>
  );
};

const Index = () => {
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const timer = setTimeout(() => {
      setLoading(false);
    }, 2500);
    
    return () => {
      clearTimeout(timer);
    };
  }, []);

  // DataSpeak chatbot integration
  useEffect(() => {
    // Set up DataSpeak configuration
    window.dataspeakChatConfiguration = {
      interfaceId: "6863892dbcf4fea86a49e9f8",
    };

    // Load DataSpeak script
    const script = document.createElement('script');
    script.src = 'https://chat.dataspeak.nl/v1/client.js';
    script.async = true;
    document.head.appendChild(script);

    // Hide the "Powered by DataSpeak AI" link
    const hideDataSpeakBranding = () => {
      const interval = setInterval(() => {
        const brandingLink = document.querySelector('a[href="https://www.dataspeak.nl"]') as HTMLElement;
        if (brandingLink) {
          brandingLink.style.display = 'none';
          clearInterval(interval);
        }
      }, 500);
      
      // Clear interval after 10 seconds to avoid infinite checking
      setTimeout(() => clearInterval(interval), 10000);
    };

    script.onload = hideDataSpeakBranding;

    return () => {
      document.head.removeChild(script);
    };
  }, []);

  // Schema markup for SEO
  const organizationSchema = {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Booming Venture",
    "description": "AI-powered marketing strategies and business consulting from Rotterdam. We help premium brands grow with performance marketing that delivers results.",
    "url": "https://boomingventure.com",
    "logo": "https://boomingventure.com/lovable-uploads/21172c30-65ed-42bf-8a4b-b92cbd2b246e.png",
    "image": "https://boomingventure.com/lovable-uploads/4e357139-5a7e-4336-8796-94013f33dc3d.png",
    "telephone": "+31 10 123 4567",
    "email": "info@boomingventure.com",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Rotterdam",
      "addressCountry": "Netherlands"
    },
    "sameAs": [
      "https://www.linkedin.com/company/booming-venture",
      "https://twitter.com/boomingventure"
    ],
    "areaServed": "Netherlands",
    "serviceType": ["Marketing Consulting", "AI Marketing", "Performance Marketing", "Business Growth"],
    "foundingDate": "2024"
  };

  const webSiteSchema = {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Booming Venture",
    "url": "https://boomingventure.com",
    "description": "Performance. Personality. Powered by AI.",
    "potentialAction": {
      "@type": "SearchAction",
      "target": "https://boomingventure.com/search?q={search_term_string}",
      "query-input": "required name=search_term_string"
    }
  };

  const serviceSchema = {
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "AI-Powered Marketing Solutions",
    "description": "Comprehensive AI-powered marketing strategies and business consulting services",
    "provider": {
      "@type": "Organization",
      "name": "Booming Venture"
    },
    "areaServed": "Netherlands",
    "serviceType": "Marketing Consulting"
  };
  
  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(organizationSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(webSiteSchema) }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(serviceSchema) }}
      />
      
      <AnimatePresence mode="wait">
        {loading && <PreLoader />}
      </AnimatePresence>
      
      <ScrollProgress />
      <ParallaxBackgrounds />
      
      <motion.div 
        className="min-h-screen flex flex-col"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.5, delay: loading ? 2.5 : 0 }}
      >
        <Navbar />
        <main className="flex-grow">
          <HeroSection />
          {/* <AwardsBanner /> */}
          <ServicesSection />
          <FreeGrowthGuide />
          <AboutSection />
          <TestimonialsSection />
          <ContactSection />
        </main>
        
        <FloatingActionButton />
      </motion.div>
    </>
  );
};

export default Index;
