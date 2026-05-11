
import { useState } from "react";
import { motion } from "framer-motion";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { ScrollProgress } from "@/components/ui/scroll-progress";
import ROIForecastForm from "@/components/roi/ROIForecastForm";
import ROIForecastResults from "@/components/roi/ROIForecastResults";

export type BusinessSegment = "ecommerce" | "b2b" | "saas" | "agency" | "other";

export type MarketingChannel = "seo" | "ads" | "social" | "email" | "direct";

export type ROIForecastData = {
  visitors: number;
  optInRate: number;
  leadToOpportunityRate: number;
  opportunityToSaleRate: number;
  aov: number;
  marketingBudget: number;
  channels: MarketingChannel[];
  segment: BusinessSegment;
  
  // Calculated fields
  leads?: number;
  opportunities?: number;
  sales?: number;
  revenue?: number;
  cac?: number;
  ltv?: number;
  roi?: number;
  
  // Potential improvements
  potentialLeads?: number;
  potentialOpportunities?: number;
  potentialSales?: number;
  potentialRevenue?: number;
  potentialCac?: number;
  potentialRoi?: number;
  revenueLoss?: number;
};

const ROIForecaster = () => {
  const [results, setResults] = useState<ROIForecastData | null>(null);
  const [webhookTriggered, setWebhookTriggered] = useState(false);

  const calculateForecast = (data: ROIForecastData) => {
    // Calculate funnel metrics
    const leads = data.visitors * (data.optInRate / 100);
    const opportunities = leads * (data.leadToOpportunityRate / 100);
    const sales = opportunities * (data.opportunityToSaleRate / 100);
    const revenue = sales * data.aov;
    
    // Calculate CAC and ROI
    const cac = data.marketingBudget / sales || 0;
    const ltv = data.aov * 2.5; // Estimate LTV as 2.5x AOV
    const roi = (revenue - data.marketingBudget) / data.marketingBudget * 100;
    
    // Calculate potential with 20% optimization at each stage
    const optInRateImproved = Math.min(data.optInRate * 1.2, 100);
    const leadToOpportunityRateImproved = Math.min(data.leadToOpportunityRate * 1.2, 100);
    const opportunityToSaleRateImproved = Math.min(data.opportunityToSaleRate * 1.2, 100);
    
    const potentialLeads = data.visitors * (optInRateImproved / 100);
    const potentialOpportunities = potentialLeads * (leadToOpportunityRateImproved / 100);
    const potentialSales = potentialOpportunities * (opportunityToSaleRateImproved / 100);
    const potentialRevenue = potentialSales * data.aov;
    
    const potentialCac = data.marketingBudget / potentialSales || 0;
    const potentialRoi = (potentialRevenue - data.marketingBudget) / data.marketingBudget * 100;
    
    // Calculate revenue loss
    const revenueLoss = potentialRevenue - revenue;
    
    setResults({
      ...data,
      leads,
      opportunities,
      sales,
      revenue,
      cac,
      ltv,
      roi,
      potentialLeads,
      potentialOpportunities,
      potentialSales,
      potentialRevenue,
      potentialCac,
      potentialRoi,
      revenueLoss
    });
  };

  const triggerWebhook = async (data: ROIForecastData) => {
    try {
      // This is a mock webhook implementation
      // In a real scenario, you would make an API call to your CRM/email system
      console.log("Webhook triggered with data:", data);
      setWebhookTriggered(true);
      
      // Reset webhook state after 3 seconds
      setTimeout(() => {
        setWebhookTriggered(false);
      }, 3000);
      
      return true;
    } catch (error) {
      console.error("Error triggering webhook:", error);
      return false;
    }
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
            ROI Forecaster
          </h1>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Discover hidden revenue in your marketing funnel and forecast your ROI with AI-powered optimization.
          </p>
        </motion.div>

        <div className="grid md:grid-cols-2 gap-8 mt-8">
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
          >
            <ROIForecastForm onCalculate={calculateForecast} />
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.4 }}
          >
            {results ? (
              <ROIForecastResults 
                data={results} 
                onTriggerWebhook={triggerWebhook}
                webhookTriggered={webhookTriggered}
              />
            ) : (
              <div className="h-full flex items-center justify-center bg-muted/30 rounded-lg p-8">
                <div className="text-center text-muted-foreground">
                  <h3 className="text-xl font-medium mb-2">Enter your funnel metrics</h3>
                  <p>Fill out the form and calculate to see your ROI forecast and optimization potential</p>
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

export default ROIForecaster;
