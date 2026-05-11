
import { useState } from "react";
import { motion } from "framer-motion";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import FunnelForm from "@/components/funnel/FunnelForm";
import FunnelResults from "@/components/funnel/FunnelResults";
import { ScrollProgress } from "@/components/ui/scroll-progress";

export type FunnelData = {
  visitors: number;
  optInRate: number;
  conversionRate: number;
  aov: number;
  optIns?: number;
  sales?: number;
  revenue?: number;
  lostOptIns?: number;
  lostSales?: number;
  lostRevenue?: number;
};

const FunnelCalculator = () => {
  const [results, setResults] = useState<FunnelData | null>(null);

  const calculateResults = (data: FunnelData) => {
    const benchmarkOptIn = 25; // %
    const benchmarkConversion = 10; // %

    // Calculate funnel metrics
    const optIns = data.visitors * (data.optInRate / 100);
    const sales = optIns * (data.conversionRate / 100);
    const revenue = sales * data.aov;

    // Calculate lost opportunities
    const lostOptIns = data.visitors * ((benchmarkOptIn - data.optInRate) / 100);
    const lostSales = lostOptIns * (benchmarkConversion / 100);
    const lostRevenue = lostSales * data.aov;

    setResults({
      ...data,
      optIns,
      sales,
      revenue,
      lostOptIns: lostOptIns > 0 ? lostOptIns : 0,
      lostSales: lostSales > 0 ? lostSales : 0,
      lostRevenue: lostRevenue > 0 ? lostRevenue : 0
    });
  };

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <ScrollProgress />
      <Navbar />
      
      <main className="flex-grow container mx-auto px-4 py-12">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="mb-12 text-center"
        >
          <h1 className="gradient-text text-4xl md:text-5xl font-bold mb-4">
            <br></br>
            Funnel Leak Detector
          </h1>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Identify where your sales funnel is leaking money and discover how much revenue you're leaving on the table.
          </p>
        </motion.div>

        <div className="grid md:grid-cols-2 gap-8 mt-8">
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
          >
            <FunnelForm onCalculate={calculateResults} />
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.4 }}
          >
            {results ? (
              <FunnelResults data={results} />
            ) : (
              <div className="h-full flex items-center justify-center bg-muted/30 rounded-lg p-8">
                <div className="text-center text-muted-foreground">
                  <h3 className="text-xl font-medium mb-2">Enter your funnel metrics</h3>
                  <p>Fill out the form and click "Calculate" to see your results</p>
                </div>
              </div>
            )}
          </motion.div>
        </div>
      </main>
      
      <Footer />
    </div>
  );
};

export default FunnelCalculator;
