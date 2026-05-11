
import { motion } from "framer-motion";
import type { ROIForecastData } from "@/pages/ROIForecaster";

interface ROIMetricsPanelProps {
  data: ROIForecastData;
}

const formatNumber = (num: number, decimals = 0) => {
  if (!num && num !== 0) return "—";
  
  if (num >= 1000000) {
    return `${(num / 1000000).toFixed(1)}M`;
  } else if (num >= 1000) {
    return `${(num / 1000).toFixed(1)}K`;
  } else {
    return num.toFixed(decimals).toLocaleString();
  }
};

const formatCurrency = (num: number) => {
  if (!num && num !== 0) return "—";
  
  if (num >= 1000000) {
    return `€${(num / 1000000).toFixed(1)}M`;
  } else if (num >= 1000) {
    return `€${(num / 1000).toFixed(1)}K`;
  } else {
    return `€${Math.round(num).toLocaleString()}`;
  }
};

const formatPercentage = (num: number) => {
  if (!num && num !== 0) return "—";
  return `${num.toFixed(1)}%`;
};

const ROIMetricsPanel = ({ data }: ROIMetricsPanelProps) => {
  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-xl font-semibold mb-3 gradient-text">Current Performance</h3>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.1 }}
            className="bg-muted/30 p-4 rounded-lg text-center"
          >
            <p className="text-sm text-muted-foreground">CAC</p>
            <p className="text-xl font-bold">{formatCurrency(data.cac || 0)}</p>
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.2 }}
            className="bg-muted/30 p-4 rounded-lg text-center"
          >
            <p className="text-sm text-muted-foreground">LTV</p>
            <p className="text-xl font-bold">{formatCurrency(data.ltv || 0)}</p>
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.3 }}
            className="bg-muted/30 p-4 rounded-lg text-center"
          >
            <p className="text-sm text-muted-foreground">LTV:CAC</p>
            <p className="text-xl font-bold">{data.ltv && data.cac ? formatNumber(data.ltv / data.cac, 1) : "—"}</p>
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.4 }}
            className="bg-muted/30 p-4 rounded-lg text-center"
          >
            <p className="text-sm text-muted-foreground">ROI</p>
            <p className="text-xl font-bold">{formatPercentage(data.roi || 0)}</p>
          </motion.div>
        </div>
      </div>
      
      <div>
        <h3 className="text-xl font-semibold mb-3 text-venture-600">Optimization Potential</h3>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.5 }}
            className="bg-venture-50 p-4 rounded-lg text-center border border-venture-200"
          >
            <p className="text-sm text-muted-foreground">Potential Sales</p>
            <p className="text-xl font-bold">{formatNumber(data.potentialSales || 0)}</p>
            {data.sales && data.potentialSales && (
              <p className="text-xs text-venture-600">
                +{formatNumber(data.potentialSales - data.sales)} (+{formatPercentage(((data.potentialSales - data.sales) / data.sales) * 100)})
              </p>
            )}
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.6 }}
            className="bg-venture-50 p-4 rounded-lg text-center border border-venture-200"
          >
            <p className="text-sm text-muted-foreground">Potential Revenue</p>
            <p className="text-xl font-bold">{formatCurrency(data.potentialRevenue || 0)}</p>
            {data.revenue && data.potentialRevenue && (
              <p className="text-xs text-venture-600">
                +{formatCurrency(data.potentialRevenue - data.revenue)}
              </p>
            )}
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.7 }}
            className="bg-venture-50 p-4 rounded-lg text-center border border-venture-200"
          >
            <p className="text-sm text-muted-foreground">Potential CAC</p>
            <p className="text-xl font-bold">{formatCurrency(data.potentialCac || 0)}</p>
            {data.cac && data.potentialCac && (
              <p className="text-xs text-venture-600">
                -{formatCurrency(data.cac - data.potentialCac)}
              </p>
            )}
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, delay: 0.8 }}
            className="bg-venture-50 p-4 rounded-lg text-center border border-venture-200"
          >
            <p className="text-sm text-muted-foreground">Potential ROI</p>
            <p className="text-xl font-bold">{formatPercentage(data.potentialRoi || 0)}</p>
            {data.roi && data.potentialRoi && (
              <p className="text-xs text-venture-600">
                +{formatPercentage(data.potentialRoi - data.roi)}
              </p>
            )}
          </motion.div>
        </div>
      </div>
    </div>
  );
};

export default ROIMetricsPanel;
