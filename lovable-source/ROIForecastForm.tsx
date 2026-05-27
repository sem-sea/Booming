
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { TrendingUp } from "lucide-react";
import { Checkbox } from "@/components/ui/checkbox";
import type { ROIForecastData, BusinessSegment, MarketingChannel } from "@/pages/ROIForecaster";

interface ROIForecastFormProps {
  onCalculate: (data: ROIForecastData) => void;
}

const ROIForecastForm = ({ onCalculate }: ROIForecastFormProps) => {
  const [formData, setFormData] = useState<ROIForecastData>({
    visitors: 10000,
    optInRate: 15,
    leadToOpportunityRate: 25,
    opportunityToSaleRate: 20,
    aov: 1500,
    marketingBudget: 5000,
    channels: ["seo", "ads"],
    segment: "b2b",
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.visitors || formData.visitors <= 0) {
      newErrors.visitors = "Visitors must be a positive number";
    }
    
    if (!formData.optInRate || formData.optInRate <= 0 || formData.optInRate > 100) {
      newErrors.optInRate = "Opt-in rate must be between 0 and 100";
    }
    
    if (!formData.leadToOpportunityRate || formData.leadToOpportunityRate <= 0 || formData.leadToOpportunityRate > 100) {
      newErrors.leadToOpportunityRate = "Lead-to-opportunity rate must be between 0 and 100";
    }
    
    if (!formData.opportunityToSaleRate || formData.opportunityToSaleRate <= 0 || formData.opportunityToSaleRate > 100) {
      newErrors.opportunityToSaleRate = "Opportunity-to-sale rate must be between 0 and 100";
    }
    
    if (!formData.aov || formData.aov <= 0) {
      newErrors.aov = "Average order value must be a positive number";
    }
    
    if (!formData.marketingBudget || formData.marketingBudget < 0) {
      newErrors.marketingBudget = "Marketing budget must be a positive number";
    }

    if (!formData.channels || formData.channels.length === 0) {
      newErrors.channels = "Please select at least one marketing channel";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (validateForm()) {
      onCalculate(formData);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: parseFloat(value) || 0,
    }));
  };

  const handleChannelChange = (channel: MarketingChannel) => {
    setFormData((prev) => {
      const channelExists = prev.channels.includes(channel);
      
      const updatedChannels = channelExists
        ? prev.channels.filter(c => c !== channel)
        : [...prev.channels, channel];
      
      return {
        ...prev,
        channels: updatedChannels
      };
    });
  };

  const handleSegmentChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const segment = e.target.value as BusinessSegment;
    setFormData(prev => ({
      ...prev,
      segment
    }));
  };

  const channelOptions: { value: MarketingChannel; label: string }[] = [
    { value: "seo", label: "SEO" },
    { value: "ads", label: "Paid Ads" },
    { value: "social", label: "Social Media" },
    { value: "email", label: "Email Marketing" },
    { value: "direct", label: "Direct Traffic" },
  ];

  const segmentOptions: { value: BusinessSegment; label: string }[] = [
    { value: "ecommerce", label: "eCommerce" },
    { value: "b2b", label: "B2B" },
    { value: "saas", label: "SaaS" },
    { value: "agency", label: "Agency" },
    { value: "other", label: "Other" },
  ];

  return (
    <Card className="shadow-lg border-t-4 border-t-venture-500">
      <CardHeader>
        <CardTitle className="text-2xl">ROI Forecaster</CardTitle>
        <CardDescription>
          Enter your funnel metrics to discover hidden revenue and forecast your ROI
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-4">
            <div>
              <Label htmlFor="segment" className="text-base">
                Business Segment
              </Label>
              <select
                id="segment"
                value={formData.segment}
                onChange={handleSegmentChange}
                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {segmentOptions.map(option => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <Label htmlFor="visitors" className="text-base">
                Monthly Website Visitors
              </Label>
              <Input
                id="visitors"
                name="visitors"
                type="number"
                placeholder="10,000"
                value={formData.visitors}
                onChange={handleChange}
                className={errors.visitors ? "border-destructive" : ""}
              />
              {errors.visitors && (
                <p className="text-sm text-destructive mt-1">{errors.visitors}</p>
              )}
            </div>

            <div>
              <Label htmlFor="optInRate" className="text-base">
                Opt-in Rate (%)
              </Label>
              <Input
                id="optInRate"
                name="optInRate"
                type="number"
                placeholder="15"
                value={formData.optInRate}
                onChange={handleChange}
                className={errors.optInRate ? "border-destructive" : ""}
              />
              {errors.optInRate && (
                <p className="text-sm text-destructive mt-1">{errors.optInRate}</p>
              )}
            </div>

            <div>
              <Label htmlFor="leadToOpportunityRate" className="text-base">
                Lead-to-Opportunity Rate (%)
              </Label>
              <Input
                id="leadToOpportunityRate"
                name="leadToOpportunityRate"
                type="number"
                placeholder="25"
                value={formData.leadToOpportunityRate}
                onChange={handleChange}
                className={errors.leadToOpportunityRate ? "border-destructive" : ""}
              />
              {errors.leadToOpportunityRate && (
                <p className="text-sm text-destructive mt-1">{errors.leadToOpportunityRate}</p>
              )}
            </div>

            <div>
              <Label htmlFor="opportunityToSaleRate" className="text-base">
                Opportunity-to-Sale Rate (%)
              </Label>
              <Input
                id="opportunityToSaleRate"
                name="opportunityToSaleRate"
                type="number"
                placeholder="20"
                value={formData.opportunityToSaleRate}
                onChange={handleChange}
                className={errors.opportunityToSaleRate ? "border-destructive" : ""}
              />
              {errors.opportunityToSaleRate && (
                <p className="text-sm text-destructive mt-1">{errors.opportunityToSaleRate}</p>
              )}
            </div>

            <div>
              <Label htmlFor="aov" className="text-base">
                Average Order Value (€)
              </Label>
              <Input
                id="aov"
                name="aov"
                type="number"
                placeholder="1500"
                value={formData.aov}
                onChange={handleChange}
                className={errors.aov ? "border-destructive" : ""}
              />
              {errors.aov && (
                <p className="text-sm text-destructive mt-1">{errors.aov}</p>
              )}
            </div>

            <div>
              <Label htmlFor="marketingBudget" className="text-base">
                Marketing Budget/Month (€)
              </Label>
              <Input
                id="marketingBudget"
                name="marketingBudget"
                type="number"
                placeholder="5000"
                value={formData.marketingBudget}
                onChange={handleChange}
                className={errors.marketingBudget ? "border-destructive" : ""}
              />
              {errors.marketingBudget && (
                <p className="text-sm text-destructive mt-1">{errors.marketingBudget}</p>
              )}
            </div>

            <div>
              <Label className="text-base">
                Marketing Channels
              </Label>
              <div className="grid grid-cols-2 gap-2 mt-2">
                {channelOptions.map(channel => (
                  <div key={channel.value} className="flex items-center space-x-2">
                    <Checkbox 
                      id={`channel-${channel.value}`} 
                      checked={formData.channels.includes(channel.value)}
                      onCheckedChange={() => handleChannelChange(channel.value)}
                    />
                    <label 
                      htmlFor={`channel-${channel.value}`}
                      className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                      {channel.label}
                    </label>
                  </div>
                ))}
              </div>
              {errors.channels && (
                <p className="text-sm text-destructive mt-1">{errors.channels}</p>
              )}
            </div>
          </div>

          <Button 
            type="submit" 
            size="lg" 
            className="w-full bg-gradient-to-r from-venture-600 to-booming-600 hover:from-venture-700 hover:to-booming-700"
          >
            <TrendingUp className="mr-2" />
            Calculate ROI Forecast
          </Button>
        </form>
      </CardContent>
    </Card>
  );
};

export default ROIForecastForm;
