
import { formatNumber, formatCurrency } from "@/utils/formatNumbers";
import type { FunnelData } from "@/pages/FunnelCalculator";

interface FunnelPerformanceProps {
  data: FunnelData;
}

const FunnelPerformance = ({ data }: FunnelPerformanceProps) => {
  return (
    <div>
      <h3 className="text-xl font-semibold mb-3 text-venture-600">Current Performance</h3>
      <div className="grid grid-cols-3 gap-4">
        <div className="bg-gradient-to-br from-venture-50 to-venture-100 p-4 rounded-lg text-center border border-venture-200">
          <p className="text-sm text-venture-600 font-medium">Opt-ins</p>
          <p className="text-2xl font-bold text-venture-700">{formatNumber(data.optIns || 0)}</p>
          <p className="text-xs text-venture-500 mt-1">from {formatNumber(data.visitors)} visitors</p>
        </div>
        <div className="bg-gradient-to-br from-venture-50 to-venture-100 p-4 rounded-lg text-center border border-venture-200">
          <p className="text-sm text-venture-600 font-medium">Sales</p>
          <p className="text-2xl font-bold text-venture-700">{formatNumber(data.sales || 0)}</p>
          <p className="text-xs text-venture-500 mt-1">{data.conversionRate}% conversion</p>
        </div>
        <div className="bg-gradient-to-br from-venture-50 to-venture-100 p-4 rounded-lg text-center border border-venture-200">
          <p className="text-sm text-venture-600 font-medium">Revenue</p>
          <p className="text-2xl font-bold text-venture-700">{formatCurrency(data.revenue || 0)}</p>
          <p className="text-xs text-venture-500 mt-1">monthly revenue</p>
        </div>
      </div>
    </div>
  );
};

export default FunnelPerformance;
