
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { motion } from "framer-motion";

const TermsOfService = () => {
  const lastUpdated = "January 15, 2025";

  return (
    <div className="min-h-screen">
      <Navbar />
      
      <section className="pt-24 pb-16">
        <div className="container mx-auto px-4 md:px-6 max-w-4xl">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
          >
            <h1 className="text-4xl md:text-5xl font-bold mb-6">Terms of Service</h1>
            <p className="text-lg text-muted-foreground mb-8">
              Last updated: {lastUpdated}
            </p>

            <div className="prose prose-lg max-w-none space-y-8">
              <section>
                <h2 className="text-2xl font-bold mb-4">1. Acceptance of Terms</h2>
                <p>
                  By accessing and using Booming Venture's services, you accept and agree to be bound 
                  by the terms and provision of this agreement. If you do not agree to abide by the 
                  above, please do not use this service.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">2. Services Description</h2>
                <p className="mb-4">
                  Booming Venture provides digital marketing services including but not limited to:
                </p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>AI-powered marketing strategy development</li>
                  <li>Marketing funnel optimization</li>
                  <li>ROI forecasting and analysis</li>
                  <li>Growth consulting and implementation</li>
                  <li>Digital marketing campaign management</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">3. Payment Terms</h2>
                <p className="mb-4">
                  <strong>Payment is due within 7 days of invoice date</strong> unless otherwise agreed 
                  in writing. Late payments may incur interest charges of 1.5% per month.
                </p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>All fees are non-refundable unless explicitly stated otherwise</li>
                  <li>Payments must be made in EUR via bank transfer or approved payment methods</li>
                  <li>Services may be suspended for accounts with overdue payments</li>
                  <li>Client is responsible for all bank charges and transaction fees</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">4. Client Responsibilities</h2>
                <p className="mb-4">The client agrees to:</p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Provide accurate and complete information necessary for service delivery</li>
                  <li>Respond to communications within 48 hours during business days</li>
                  <li>Provide timely access to required systems, accounts, and personnel</li>
                  <li>Pay all invoices according to agreed payment terms</li>
                  <li>Comply with all applicable laws and regulations</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">5. Intellectual Property</h2>
                <p className="mb-4">
                  <strong>Work Product:</strong> Upon full payment, client receives rights to 
                  deliverables specifically created for their project. Booming Venture retains 
                  rights to methodologies, processes, and general knowledge.
                </p>
                <p>
                  <strong>Pre-existing IP:</strong> Each party retains ownership of their 
                  pre-existing intellectual property.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">6. Limitation of Liability</h2>
                <p className="mb-4">
                  <strong>TO THE MAXIMUM EXTENT PERMITTED BY LAW:</strong>
                </p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Our total liability shall not exceed the fees paid by client in the 12 months preceding the claim</li>
                  <li>We are not liable for indirect, consequential, or punitive damages</li>
                  <li>We do not guarantee specific results or ROI from marketing activities</li>
                  <li>Client acknowledges that marketing results depend on many factors beyond our control</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">7. Termination</h2>
                <p className="mb-4">
                  Either party may terminate services with 30 days written notice. 
                  Immediate termination is allowed for:
                </p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Material breach of contract (with 7 days cure period)</li>
                  <li>Non-payment of fees</li>
                  <li>Bankruptcy or insolvency</li>
                </ul>
                <p className="mt-4">
                  <strong>Upon termination:</strong> Client must pay all outstanding fees. 
                  Work completed to termination date will be delivered upon payment.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">8. Confidentiality</h2>
                <p>
                  Both parties agree to maintain confidentiality of proprietary information 
                  shared during the course of services. This obligation survives termination 
                  of the agreement.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">9. Force Majeure</h2>
                <p>
                  Neither party shall be liable for delays or failures due to circumstances 
                  beyond reasonable control, including but not limited to natural disasters, 
                  government actions, or technical failures.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">10. Governing Law</h2>
                <p>
                  These terms are governed by Dutch law. Any disputes shall be resolved 
                  through the competent courts of Rotterdam, The Netherlands.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">11. Contact Information</h2>
                <div className="bg-gray-50 p-4 rounded-lg">
                  <p><strong>Booming Venture</strong></p>
                  <p>Email: legal@boomingventure.com</p>
                  <p>Address: Breedveldsingel 1, 3055 PG Rotterdam, The Netherlands</p>
                  <p>Chamber of Commerce: [Your KvK number]</p>
                </div>
              </section>
            </div>
          </motion.div>
        </div>
      </section>
      
      <Footer />
    </div>
  );
};

export default TermsOfService;
