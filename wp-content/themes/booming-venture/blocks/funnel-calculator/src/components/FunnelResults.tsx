
import { useRef } from "react";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { PhoneCall } from "lucide-react";
import FunnelChart from "./FunnelChart";
import FunnelPerformance from "./FunnelPerformance";
import FunnelLostOpportunities from "./FunnelLostOpportunities";
import type { FunnelData } from "@/pages/FunnelCalculator";
import { addContactToBrevo } from "@/services/brevoService";
import { useToast } from "@/hooks/use-toast";

interface FunnelResultsProps {
  data: FunnelData;
}

const FunnelResults = ({ data }: FunnelResultsProps) => {
  const { toast } = useToast();

  const handleBookCall = async () => {
    // Track funnel calculator usage in Brevo
    try {
      console.log("Funnel analysis completed - user clicked Book a Call");
      
      // Navigate to homepage contact section
      window.location.href = "/#contact";
    } catch (error) {
      console.error("Error tracking funnel completion:", error);
      // Still redirect even if tracking fails
      window.location.href = "/#contact";
    }
  };

  return (
    <div>
      <Card className="shadow-lg border-t-4 border-t-venture-500">
        <CardHeader>
          <CardTitle className="text-2xl">Your Funnel Analysis</CardTitle>
        </CardHeader>
        <CardContent className="space-y-8">
          <FunnelChart data={data} />
          
          <div className="space-y-6">
            <FunnelPerformance data={data} />
            <FunnelLostOpportunities data={data} />
          </div>
        </CardContent>
        <CardFooter className="flex flex-col space-y-4">
          <p className="text-muted-foreground text-sm italic">
            Analysis based on industry benchmarks: 25% opt-in rate and 10% conversion rate
          </p>
          <Button 
            className="w-full bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700"
            size="lg"
            onClick={handleBookCall}
          >
            <PhoneCall className="mr-2" />
            Book a Call
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
};

export default FunnelResults;
