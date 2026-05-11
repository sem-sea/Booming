
import { formatNumber, formatCurrency } from "@/utils/formatNumbers";
import type { FunnelData } from "@/pages/FunnelCalculator";

interface FunnelLostOpportunitiesProps {
  data: FunnelData;
}

const FunnelLostOpportunities = ({ data }: FunnelLostOpportunitiesProps) => {
  if (!data.lostOptIns && !data.lostRevenue) return null;

  return (
    <div>
      <h3 className="text-xl font-semibold mb-3 text-destructive">Lost Opportunities</h3>
      <div className="grid grid-cols-3 gap-4">
        <div className="bg-destructive/5 p-4 rounded-lg text-center border border-destructive/20">
          <p className="text-sm text-destructive/80 font-medium">Lost Leads</p>
          <p className="text-2xl font-bold text-destructive">{formatNumber(data.lostOptIns || 0)}</p>
          <p className="text-xs text-destructive/70 mt-1">potential leads missed</p>
        </div>
        <div className="bg-destructive/5 p-4 rounded-lg text-center border border-destructive/20">
          <p className="text-sm text-destructive/80 font-medium">Lost Sales</p>
          <p className="text-2xl font-bold text-destructive">{formatNumber(data.lostSales || 0)}</p>
          <p className="text-xs text-destructive/70 mt-1">missed opportunities</p>
        </div>
        <div className="bg-destructive/10 p-4 rounded-lg text-center border border-destructive/30">
          <p className="text-sm text-destructive/80 font-medium">Lost Revenue</p>
          <p className="text-2xl font-bold text-destructive">{formatCurrency(data.lostRevenue || 0)}</p>
          <p className="text-xs text-destructive/70 mt-1">monthly revenue lost</p>
        </div>
      </div>
    </div>
  );
};

export default FunnelLostOpportunities;
