
import { motion } from "framer-motion";
import type { FunnelData } from "@/pages/FunnelCalculator";
import { Users, UserCircle, DollarSign } from "lucide-react";

interface FunnelChartProps {
  data: FunnelData;
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
    return `$${(num / 1000000).toFixed(1)}M`;
  } else if (num >= 1000) {
    return `$${(num / 1000).toFixed(1)}K`;
  } else {
    return `$${Math.round(num).toLocaleString()}`;
  }
};

const calculatePercentage = (part: number, whole: number) => {
  return Math.round((part / whole) * 100) || 0;
};

const FunnelChart = ({ data }: FunnelChartProps) => {
  const { visitors, optIns = 0, sales = 0, optInRate, conversionRate } = data;
  
  // Ensure minimum width for better visibility on mobile
  const optInWidth = Math.max(50, Math.min(90, optInRate * 3));
  const conversionWidth = Math.max(30, Math.min(70, conversionRate * 5));
  
  return (
    <div className="relative mx-auto my-8 h-[300px] flex flex-col items-center justify-between">
      {/* Visitors level */}
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: "100%" }}
        transition={{ duration: 0.7 }}
        className="w-full h-20 bg-gradient-to-r from-booming-100 to-booming-200 rounded-lg flex items-center justify-between px-4 relative z-30"
      >
        <div className="flex items-center">
          <Users className="h-8 w-8 text-booming-600 mr-3 flex-shrink-0" />
          <div>
            <p className="font-medium">Visitors</p>
            <p className="text-xl font-bold truncate min-w-[80px] overflow-hidden">{formatNumber(visitors)}</p>
          </div>
        </div>
        <div className="text-right min-w-[80px] flex-shrink-0">
          <p className="text-sm text-muted-foreground">Conversion</p>
          <p className="font-bold">{optInRate}%</p>
        </div>
      </motion.div>
      
      {/* Trapezoid connecting visitors to leads */}
      <motion.div 
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.7, delay: 0.2 }}
        className="w-full h-10 relative"
        style={{
          clipPath: `polygon(0 0, 100% 0, ${optInWidth}% 100%, ${(100 - optInWidth) / 2}% 100%)`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-booming-200 to-booming-300"></div>
      </motion.div>
      
      {/* Opt-ins level */}
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: `${optInWidth}%` }}
        transition={{ duration: 0.7, delay: 0.4 }}
        className={`h-20 bg-gradient-to-r from-booming-300 to-venture-300 rounded-lg flex items-center justify-between px-4 relative z-20`}
      >
        <div className="flex items-center">
          <UserCircle className="h-8 w-8 text-venture-600 mr-3 flex-shrink-0" />
          <div>
            <p className="font-medium">Opt-ins</p>
            <p className="text-xl font-bold truncate min-w-[80px] overflow-hidden">{formatNumber(optIns)}</p>
          </div>
        </div>
        <div className="text-right min-w-[80px] flex-shrink-0">
          <p className="text-sm text-muted-foreground">Conversion</p>
          <p className="font-bold">{conversionRate}%</p>
        </div>
      </motion.div>
      
      {/* Trapezoid connecting leads to sales */}
      <motion.div 
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.7, delay: 0.6 }}
        className={`h-10 relative`}
        style={{
          width: `${optInWidth}%`,
          clipPath: `polygon(0 0, 100% 0, ${conversionWidth / optInWidth * 100}% 100%, ${(optInWidth - conversionWidth) / 2 / optInWidth * 100}% 100%)`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-venture-300 to-venture-400"></div>
      </motion.div>
      
      {/* Sales level */}
      <motion.div 
        initial={{ opacity: 0, width: 0 }}
        animate={{ opacity: 1, width: `${conversionWidth}%` }}
        transition={{ duration: 0.7, delay: 0.8 }}
        className={`h-20 bg-gradient-to-r from-venture-500 to-venture-600 rounded-lg flex items-center justify-between px-4 relative z-10 text-white`}
      >
        <div className="flex items-center overflow-hidden">
          <DollarSign className="h-8 w-8 text-white mr-3 flex-shrink-0" />
          <div>
            <p className="font-medium">Sales</p>
            <p className="text-xl font-bold truncate min-w-[80px] overflow-hidden">{formatNumber(sales)}</p>
          </div>
        </div>
        <div className="text-right min-w-[80px] flex-shrink-0">
          <p className="text-sm text-white/70">Revenue</p>
          <p className="font-bold">{formatCurrency(data.revenue || 0)}</p>
        </div>
      </motion.div>
      
      {/* Loss indications */}
      {data.lostOptIns && data.lostOptIns > 0 && (
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5, delay: 1 }}
          className="absolute top-28 right-4 bg-destructive/10 text-destructive px-3 py-1 rounded-full text-sm font-medium"
        >
          {formatNumber(data.lostOptIns)} lost leads
        </motion.div>
      )}
      
      {data.lostRevenue && data.lostRevenue > 0 && (
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.5, delay: 1.2 }}
          className="absolute bottom-4 right-4 bg-destructive/20 text-destructive px-3 py-1 rounded-full text-sm font-medium"
        >
          {formatCurrency(data.lostRevenue)} lost revenue
        </motion.div>
      )}
    </div>
  );
};

export default FunnelChart;
