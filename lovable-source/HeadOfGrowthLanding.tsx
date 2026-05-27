import React from 'react';
import { motion } from 'framer-motion';
import HeadOfGrowthHero from '@/components/sections/HeadOfGrowthHero';
import PainAmplifier from '@/components/sections/PainAmplifier';
import SolutionSection from '@/components/sections/SolutionSection';
import HowItWorksSection from '@/components/sections/HowItWorksSection';
import ProofSection from '@/components/sections/ProofSection';
import UrgencySection from '@/components/sections/UrgencySection';
import FinalCTA from '@/components/sections/FinalCTA';

const HeadOfGrowthLanding = () => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-booming-50 to-venture-50">
      <HeadOfGrowthHero />
      <PainAmplifier />
      <SolutionSection />
      <HowItWorksSection />
      <ProofSection />
      <UrgencySection />
      <FinalCTA />
    </div>
  );
};

export default HeadOfGrowthLanding;