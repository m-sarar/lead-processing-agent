import { auth, signIn, signOut } from '@/auth';
import { LeadForm } from '@/components/lead-form';
import { Bot, LogOut, Workflow } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default async function Home() {
  const session = await auth();

  return (
    <main className="p-8">
      <div className="flex items-center justify-center gap-2 mb-8">
        <h1 className="text-2xl font-bold text-center">Lead Agent</h1>
        <div className="flex items-center justify-center">
          <Bot />
          <Workflow />
        </div>
      </div>

      {session ? (
        <div className="space-y-4">
          <div className="flex items-center justify-between max-w-xl mx-auto bg-muted p-4 rounded-lg">
            <div className="flex items-center gap-3">
              {session.user?.image && (
                <img
                  src={session.user.image}
                  alt={session.user.name || 'User'}
                  className="w-10 h-10 rounded-full"
                />
              )}
              <div>
                <p className="font-medium">{session.user?.name}</p>
                <p className="text-sm text-muted-foreground">{session.user?.email}</p>
              </div>
            </div>
            <form
              action={async () => {
                'use server';
                await signOut();
              }}
            >
              <Button type="submit" variant="outline" size="sm">
                <LogOut className="w-4 h-4 mr-2" />
                Sign Out
              </Button>
            </form>
          </div>
          <LeadForm />
        </div>
      ) : (
        <div className="flex flex-col items-center justify-center max-w-md mx-auto space-y-6">
          <p className="text-muted-foreground text-center">
            Please sign in with your Google account to access the lead form.
          </p>
          <form
            action={async () => {
              'use server';
              await signIn('google', { redirectTo: '/' });
            }}
          >
            <Button type="submit" size="lg">
              Sign in with Google
            </Button>
          </form>
        </div>
      )}
    </main>
  );
}
