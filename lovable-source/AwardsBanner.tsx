
import { motion } from "framer-motion";
import { Award, Star, Trophy, Zap } from "lucide-react";
import Lottie from "react-lottie-player";
import awardLottie from "@/assets/award-lottie.json";

const AwardsBanner = () => {
  const awards = [
    {
      icon: Trophy,
      name: "Agency of the Year",
      organization: "Marketing Excellence Awards"
    },
    {
      icon: Award,
      name: "Best AI Implementation",
      organization: "Digital Innovation Summit"
    },
    {
      icon: Star,
      name: "Top Growth Agency",
      organization: "Business Growth Forum"
    },
    {
      icon: Zap,
      name: "Best Client Results",
      organization: "ROI Masters"
    }
  ];

  return (
    <section className="py-12 bg-gradient-to-r from-booming-900 to-venture-900 text-white overflow-hidden relative">
      {/* 3D geometric shapes floating in background */}
      <div className="absolute inset-0 overflow-hidden">
        <motion.div 
          className="absolute h-32 w-32 rounded-full bg-booming-500/10 backdrop-blur-lg"
          animate={{
            x: [0, 30, 0],
            y: [0, 15, 0],
            scale: [1, 1.1, 1],
          }}
          transition={{
            duration: 8,
            repeat: Infinity,
            ease: "easeInOut"
          }}
          style={{ top: '15%', left: '10%' }}
        />
        <motion.div 
          className="absolute h-20 w-20 rotate-45 bg-venture-500/10 backdrop-blur-lg"
          animate={{
            x: [0, -20, 0],
            y: [0, 20, 0],
            rotate: [45, 90, 45],
          }}
          transition={{
            duration: 12,
            repeat: Infinity,
            ease: "easeInOut"
          }}
          style={{ bottom: '15%', right: '10%' }}
        />
        <motion.div 
          className="absolute h-16 w-16 rounded-md bg-white/5 backdrop-blur-lg"
          animate={{
            x: [0, 15, 0],
            y: [0, -15, 0],
            rotate: [0, 45, 0],
          }}
          transition={{
            duration: 10,
            repeat: Infinity,
            ease: "easeInOut"
          }}
          style={{ top: '50%', left: '25%' }}
        />
      </div>

      <div className="container mx-auto px-4 relative z-10">
        <div className="flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="md:w-1/4 flex items-center gap-4">
            <div className="hidden md:block w-20 h-20">
              <Lottie
                loop
                animationData={awardLottie}
                play
                style={{ width: 80, height: 80 }}
              />
            </div>
            <div>
              <h3 className="text-xl md:text-2xl font-bold bg-gradient-to-r from-white to-booming-200 bg-clip-text text-transparent">Award-Winning Agency</h3>
              <p className="text-white/70">Recognized excellence in business growth</p>
            </div>
          </div>
          
          <div className="md:w-3/4 overflow-hidden">
            <motion.div 
              className="flex gap-8" 
              animate={{
                x: [0, -1000],
              }}
              transition={{
                x: {
                  duration: 25,
                  repeat: Infinity,
                  repeatType: "loop",
                  ease: "linear",
                },
              }}
            >
              {awards.concat(awards).concat(awards).map((award, index) => (
                <motion.div 
                  key={index} 
                  className="flex items-center gap-4 bg-white/10 backdrop-blur-sm px-6 py-3 rounded-lg min-w-max"
                  whileHover={{ 
                    scale: 1.05, 
                    backgroundColor: "rgba(255,255,255,0.15)",
                    transition: { duration: 0.2 } 
                  }}
                >
                  <award.icon className="h-8 w-8 text-venture-300" />
                  <div>
                    <p className="font-semibold">{award.name}</p>
                    <p className="text-xs text-white/70">{award.organization}</p>
                  </div>
                </motion.div>
              ))}
            </motion.div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default AwardsBanner;
