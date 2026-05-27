
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { motion } from "framer-motion";

const PrivacyPolicy = () => {
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
            <h1 className="text-4xl md:text-5xl font-bold mb-6">Privacy Policy</h1>
            <p className="text-lg text-muted-foreground mb-8">
              Last updated: {lastUpdated}
            </p>

            <div className="prose prose-lg max-w-none space-y-8">
              <section>
                <h2 className="text-2xl font-bold mb-4">1. Information We Collect</h2>
                <p className="mb-4">
                  We collect information you provide directly to us, such as when you create an account, 
                  fill out a form, request our services, or communicate with us.
                </p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Personal information (name, email address, phone number)</li>
                  <li>Company information (company name, website, industry)</li>
                  <li>Communication records (messages, support requests)</li>
                  <li>Usage data (how you interact with our website and services)</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">2. How We Use Your Information</h2>
                <p className="mb-4">We use the information we collect to:</p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Provide, maintain, and improve our marketing services</li>
                  <li>Process transactions and send related information</li>
                  <li>Send you technical notices, updates, and marketing communications</li>
                  <li>Respond to your comments, questions, and customer service requests</li>
                  <li>Analyze usage patterns to improve our services</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">3. Information Sharing</h2>
                <p className="mb-4">
                  We do not sell, trade, or otherwise transfer your personal information to third parties 
                  except as described in this policy:
                </p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>With service providers who assist us in operating our business</li>
                  <li>When required by law or to protect our rights</li>
                  <li>In connection with a business transfer or acquisition</li>
                  <li>With your explicit consent</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">4. Cookies and Tracking</h2>
                <p className="mb-4">
                  We use cookies and similar technologies to enhance your experience on our website. 
                  You can control cookie preferences through our cookie banner or browser settings.
                </p>
                <p className="mb-4">Types of cookies we use:</p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Necessary cookies for basic website functionality</li>
                  <li>Analytics cookies to understand website usage</li>
                  <li>Marketing cookies for personalized advertising</li>
                  <li>Functional cookies for enhanced user experience</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">5. Data Security</h2>
                <p>
                  We implement appropriate security measures to protect your personal information 
                  against unauthorized access, alteration, disclosure, or destruction. However, 
                  no method of transmission over the internet is 100% secure.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">6. Your Rights (GDPR)</h2>
                <p className="mb-4">If you are in the European Union, you have the right to:</p>
                <ul className="list-disc pl-6 space-y-2">
                  <li>Access your personal data</li>
                  <li>Rectify inaccurate personal data</li>
                  <li>Erase your personal data</li>
                  <li>Restrict processing of your personal data</li>
                  <li>Data portability</li>
                  <li>Object to processing</li>
                  <li>Withdraw consent at any time</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">7. Data Retention</h2>
                <p>
                  We retain your personal information only for as long as necessary to fulfill 
                  the purposes outlined in this privacy policy, unless a longer retention period 
                  is required by law.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-bold mb-4">8. Contact Us</h2>
                <p className="mb-4">
                  If you have any questions about this Privacy Policy, please contact us:
                </p>
                <div className="bg-gray-50 p-4 rounded-lg">
                  <p><strong>Booming Venture</strong></p>
                  <p>Email: privacy@boomingventure.com</p>
                  <p>Address: Breedveldsingel 1, 3055 PG Rotterdam, The Netherlands</p>
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

export default PrivacyPolicy;
