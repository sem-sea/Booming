import { motion } from "framer-motion";
import type { ROIForecastData } from "@/pages/ROIForecaster";
import { Users, UserCircle, BarChart, DollarSign, TrendingDown, TrendingUp } from "lucide-react";

interface ROIFunnelChartProps {
  data: ROIForecastData;
}

const formatNumber = (num: number) => {
  if (num >= 1000000) {
    return `${(num / 1000000).toFixed(1)}M`;
  } else if (num >= 1000) {
    return `${(num / 1000).toFixed(1)}K`;
  } else {
    return Math.round(num).toLocaleString();
  }
};

const formatCurrency = (num: number) => {
  if (num >= 1000000) {
    return `€${(num / 1000000).toFixed(1)}M`;
  } else if (num >= 1000) {
    return `€${(num / 1000).toFixed(1)}K`;
  } else {
    return `€${Math.round(num).toLocaleString()}`;
  }
};

const ROIFunnelChart = ({ data }: ROIFunnelChartProps) => {
  const minStageWidth = 60;
  
  const leadsWidth = Math.max(minStageWidth, Math.min(95, data.optInRate * 3));
  const oppsWidth = Math.max(minStageWidth, Math.min(85, leadsWidth * (data.leadToOpportunityRate / 100)));
  const salesWidth = Math.max(minStageWidth, Math.min(75, oppsWidth * (data.opportunityToSaleRate / 100)));
  
  return (
    <div className="relative mx-auto my-8 h-[500px] flex flex-col items-center justify-between">
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: "100%" }}
        transition={{ duration: 0.7 }}
        className="w-full h-[70px] bg-gradient-to-r from-booming-100 to-booming-200 rounded-lg flex items-center justify-between px-4 relative z-40"
      >
        <div className="flex items-center overflow-hidden">
          <Users className="h-8 w-8 text-booming-600 mr-3 flex-shrink-0" />
          <div className="min-w-0">
            <p className="font-medium">Visitors</p>
            <p className="text-xl font-bold truncate">{formatNumber(data.visitors)}</p>
          </div>
        </div>
        <div className="text-right flex-shrink-0">
          <p className="text-sm text-muted-foreground">Conversion</p>
          <p className="font-bold">{data.optInRate}%</p>
        </div>
      </motion.div>
      
      <motion.div 
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.7, delay: 0.2 }}
        className="w-full h-12 relative"
        style={{
          clipPath: `polygon(0 0, 100% 0, ${leadsWidth}% 100%, ${(100 - leadsWidth) / 2}% 100%)`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-booming-200 to-booming-300"></div>
      </motion.div>
      
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: `${leadsWidth}%` }}
        transition={{ duration: 0.7, delay: 0.4 }}
        className={`h-[70px] bg-gradient-to-r from-booming-300 to-venture-300 rounded-lg flex items-center justify-between px-4 relative z-30`}
      >
        <div className="flex items-center overflow-hidden">
          <UserCircle className="h-8 w-8 text-venture-600 mr-3 flex-shrink-0" />
          <div className="min-w-0">
            <p className="font-medium">Leads</p>
            <p className="text-xl font-bold truncate">{formatNumber(data.leads || 0)}</p>
          </div>
        </div>
        <div className="text-right flex-shrink-0">
          <p className="text-sm text-muted-foreground">Conversion</p>
          <p className="font-bold">{data.leadToOpportunityRate}%</p>
        </div>
      </motion.div>
      
      <motion.div 
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.7, delay: 0.6 }}
        className={`h-12 relative`}
        style={{
          width: `${leadsWidth}%`,
          clipPath: `polygon(0 0, 100% 0, ${oppsWidth / leadsWidth * 100}% 100%, ${(leadsWidth - oppsWidth) / 2 / leadsWidth * 100}% 100%)`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-venture-300 to-venture-400"></div>
      </motion.div>
      
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: `${oppsWidth}%` }}
        transition={{ duration: 0.7, delay: 0.8 }}
        className={`h-[70px] bg-gradient-to-r from-venture-400 to-venture-500 rounded-lg flex items-center justify-between px-4 relative z-20 text-white`}
      >
        <div className="flex items-center overflow-hidden">
          <BarChart className="h-8 w-8 text-white mr-3 flex-shrink-0" />
          <div className="min-w-0">
            <p className="font-medium">Opportunities</p>
            <p className="text-xl font-bold truncate">{formatNumber(data.opportunities || 0)}</p>
          </div>
        </div>
        <div className="text-right flex-shrink-0">
          <p className="text-sm text-white/70">Conversion</p>
          <p className="font-bold">{data.opportunityToSaleRate}%</p>
        </div>
      </motion.div>
      
      <motion.div 
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.7, delay: 1.0 }}
        className={`h-12 relative`}
        style={{
          width: `${oppsWidth}%`,
          clipPath: `polygon(0 0, 100% 0, ${salesWidth / oppsWidth * 100}% 100%, ${(oppsWidth - salesWidth) / 2 / oppsWidth * 100}% 100%)`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-venture-500 to-venture-600"></div>
      </motion.div>
      
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: `${salesWidth}%` }}
        transition={{ duration: 0.7, delay: 1.2 }}
        className={`h-[70px] bg-gradient-to-r from-venture-600 to-venture-700 rounded-lg flex items-center justify-between px-4 relative z-10 text-white`}
      >
        <div className="flex items-center overflow-hidden">
          <DollarSign className="h-8 w-8 text-white mr-3 flex-shrink-0" />
          <div className="min-w-0">
            <p className="font-medium">Sales</p>
            <p className="text-xl font-bold truncate">{formatNumber(data.sales || 0)}</p>
          </div>
        </div>
        <div className="text-right flex-shrink-0">
          <p className="text-sm text-white/70">Revenue</p>
          <p className="font-bold">{formatCurrency(data.revenue || 0)}</p>
        </div>
      </motion.div>
      
      {data.revenueLoss && data.revenueLoss > 0 && (
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 1.4 }}
          className="w-full max-w-xl mx-auto mt-6 z-40"
        >
          <div className="bg-destructive/10 border border-destructive/30 text-destructive px-6 py-4 rounded-lg text-center shadow-lg flex flex-col items-center justify-center space-y-2">
            <TrendingDown className="h-10 w-10 text-destructive mb-2" />
            <p className="text-sm font-medium">Potential Monthly Revenue Loss</p>
            <p className="text-3xl font-bold">
              {formatCurrency(data.revenueLoss)}
            </p>
            <p className="text-xs text-muted-foreground mt-1">
              Potential revenue left on the table each month
            </p>
          </div>
        </motion.div>
      )}
    </div>
  );
};

export default ROIFunnelChart;
