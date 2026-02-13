# Future Plans - ArmaBattles Chat System

## Overview

This document outlines plans for building a native chat system directly into ArmaBattles, similar to Facebook/Steam messaging, with potential future voice chat capabilities.

## Current Infrastructure (Already Available)

✅ **Laravel Reverb** - WebSocket server for real-time communication
✅ **PostgreSQL** - Database
✅ **User authentication** - Steam login system
✅ **Alpine.js** - Frontend reactivity
✅ **Notification system** - Can be reused for chat notifications

## Database Schema

### Conversations
Stores chat conversations (1-on-1 or group).

```sql
conversations
  - id
  - type (direct_message, group, team_chat, match_chat)
  - name (for groups, nullable for DMs)
  - created_at
  - updated_at
```

### Conversation Participants
Tracks who is in each conversation.

```sql
conversation_participants
  - id
  - conversation_id
  - user_id
  - last_read_at
  - joined_at
  - left_at (nullable)
```

### Messages
Individual chat messages.

```sql
messages
  - id
  - conversation_id
  - user_id
  - content (text)
  - type (text, image, system, file)
  - attachment_path (nullable)
  - created_at
  - updated_at
  - deleted_at (soft deletes)
```

### User Presence
Tracks online/offline status.

```sql
user_presence
  - user_id (primary key)
  - status (online, away, offline)
  - last_seen_at
  - updated_at
```

## Backend Implementation (Laravel)

### Models

**Conversation Model:**
```php
class Conversation extends Model {
    public function participants()
    public function messages()
    public function latestMessage()
    public function unreadCount($userId)
    public function markAsRead($userId)
}
```

**Message Model:**
```php
class Message extends Model {
    public function conversation()
    public function user()
    public function attachments()
}
```

### Events (Laravel Reverb Broadcasting)

```php
// app/Events/MessageSent.php
class MessageSent implements ShouldBroadcast {
    public function broadcastOn() {
        return new PrivateChannel("conversation.{$this->conversationId}");
    }
}

// app/Events/UserTyping.php
class UserTyping implements ShouldBroadcast {
    public function broadcastOn() {
        return new PrivateChannel("conversation.{$this->conversationId}");
    }
}

// app/Events/UserPresenceUpdated.php
class UserPresenceUpdated implements ShouldBroadcast {
    public function broadcastOn() {
        return new PresenceChannel("online");
    }
}
```

### Controllers

```php
// app/Http/Controllers/ChatController.php
- index() // List all conversations for current user
- show($id) // Show messages in a conversation
- store(Request $request) // Send a new message
- markAsRead($id) // Mark conversation as read
- startConversation($userId) // Start DM with another user
- uploadAttachment(Request $request) // Handle file uploads

// app/Http/Controllers/ConversationController.php
- create() // Create group conversation
- addParticipant($conversationId, $userId)
- removeParticipant($conversationId, $userId)
- leave($conversationId)
```

## Frontend Implementation (Alpine.js + Tailwind)

### Chat Sidebar Component

```html
<div x-data="chatApp()" class="flex h-screen">
  <!-- Conversation List -->
  <div class="w-80 border-r border-white/5 glass-card">
    <div class="p-4">
      <input type="search"
             x-model="searchQuery"
             placeholder="Search conversations..."
             class="w-full px-4 py-2 rounded-xl bg-white/5">
    </div>

    <div class="overflow-y-auto">
      <template x-for="conv in filteredConversations" :key="conv.id">
        <div @click="openConversation(conv.id)"
             :class="conv.unread_count > 0 ? 'bg-green-600/10 border-l-4 border-green-500' : ''"
             class="p-4 hover:bg-white/5 cursor-pointer transition">

          <div class="flex items-center gap-3">
            <div class="relative">
              <img :src="conv.avatar" class="w-12 h-12 rounded-full">
              <span x-show="conv.is_online"
                    class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></span>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <span class="font-semibold text-white truncate" x-text="conv.name"></span>
                <span class="text-xs text-gray-400" x-text="conv.last_message_time"></span>
              </div>
              <p class="text-sm text-gray-400 truncate" x-text="conv.last_message"></p>
            </div>

            <span x-show="conv.unread_count > 0"
                  x-text="conv.unread_count"
                  class="px-2 py-1 bg-green-600 text-white text-xs rounded-full"></span>
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- Message Window -->
  <div class="flex-1 flex flex-col" x-show="activeConversation">
    <!-- Header -->
    <div class="p-4 border-b border-white/5 glass-card">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img :src="activeConversation.avatar" class="w-10 h-10 rounded-full">
          <div>
            <h3 class="font-semibold text-white" x-text="activeConversation.name"></h3>
            <p class="text-xs text-gray-400" x-text="activeConversation.status"></p>
          </div>
        </div>
        <div class="flex gap-2">
          <!-- Future: Voice call button -->
          <!-- Future: Video call button -->
          <button class="p-2 hover:bg-white/5 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4" x-ref="messageContainer">
      <template x-for="msg in messages" :key="msg.id">
        <div :class="msg.user_id === currentUserId ? 'flex justify-end' : 'flex justify-start'">
          <div :class="msg.user_id === currentUserId ? 'bg-green-600' : 'bg-white/5'"
               class="max-w-md px-4 py-2 rounded-2xl">
            <p class="text-sm text-white" x-text="msg.content"></p>
            <span class="text-xs opacity-70" x-text="msg.time"></span>
          </div>
        </div>
      </template>

      <!-- Typing Indicator -->
      <div x-show="userTyping" class="flex items-center gap-2 text-gray-400 text-sm">
        <div class="flex gap-1">
          <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
          <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
          <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
        </div>
        <span x-text="typingUserName + ' is typing...'"></span>
      </div>
    </div>

    <!-- Input -->
    <div class="p-4 border-t border-white/5 glass-card">
      <div class="flex items-center gap-2">
        <button @click="$refs.fileInput.click()" class="p-2 hover:bg-white/5 rounded-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
          </svg>
        </button>
        <input type="file" x-ref="fileInput" class="hidden" @change="handleFileUpload">

        <input type="text"
               x-model="newMessage"
               @keyup.enter="sendMessage()"
               @keyup="handleTyping()"
               placeholder="Type a message..."
               class="flex-1 px-4 py-2 rounded-xl bg-white/5 border border-white/10 focus:border-green-500 focus:outline-none">

        <button @click="sendMessage()"
                :disabled="!newMessage.trim()"
                class="px-4 py-2 bg-green-600 hover:bg-green-500 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</div>
```

### Alpine.js Component Logic

```javascript
function chatApp() {
    return {
        conversations: [],
        activeConversation: null,
        messages: [],
        newMessage: '',
        searchQuery: '',
        userTyping: false,
        typingUserName: '',
        currentUserId: window.userId,

        init() {
            this.loadConversations();
            this.setupWebSocket();
            this.trackPresence();
        },

        loadConversations() {
            fetch('/api/v1/conversations')
                .then(r => r.json())
                .then(data => this.conversations = data);
        },

        openConversation(id) {
            fetch(`/api/v1/conversations/${id}/messages`)
                .then(r => r.json())
                .then(data => {
                    this.messages = data;
                    this.activeConversation = this.conversations.find(c => c.id === id);
                    this.markAsRead(id);
                    this.scrollToBottom();
                });
        },

        sendMessage() {
            if (!this.newMessage.trim()) return;

            fetch(`/api/v1/conversations/${this.activeConversation.id}/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: this.newMessage })
            }).then(() => {
                this.newMessage = '';
            });
        },

        handleTyping() {
            // Throttled typing indicator
            window.Echo.private(`conversation.${this.activeConversation.id}`)
                .whisper('typing', { user: this.currentUserId });
        },

        setupWebSocket() {
            // Listen for new messages
            window.Echo.private('user.' + this.currentUserId)
                .listen('MessageSent', (e) => {
                    if (this.activeConversation?.id === e.message.conversation_id) {
                        this.messages.push(e.message);
                        this.scrollToBottom();
                    } else {
                        // Update unread count in sidebar
                        const conv = this.conversations.find(c => c.id === e.message.conversation_id);
                        if (conv) conv.unread_count++;
                    }
                    this.playNotificationSound();
                });

            // Listen for typing indicators
            if (this.activeConversation) {
                window.Echo.private(`conversation.${this.activeConversation.id}`)
                    .listenForWhisper('typing', (e) => {
                        if (e.user !== this.currentUserId) {
                            this.userTyping = true;
                            setTimeout(() => this.userTyping = false, 3000);
                        }
                    });
            }
        },

        trackPresence() {
            window.Echo.join('online')
                .here((users) => {
                    // Update online status for all users
                })
                .joining((user) => {
                    // User came online
                })
                .leaving((user) => {
                    // User went offline
                });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                this.$refs.messageContainer.scrollTop = this.$refs.messageContainer.scrollHeight;
            });
        },

        playNotificationSound() {
            new Audio('/sounds/message.mp3').play();
        },

        get filteredConversations() {
            if (!this.searchQuery) return this.conversations;
            return this.conversations.filter(c =>
                c.name.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        }
    }
}
```

## Feature Rollout Plan

### Phase 1: MVP (2-3 days)
**Goal:** Basic functional chat system

- ✅ Direct messages (1-on-1 chat)
- ✅ Text messages only
- ✅ Unread count badges
- ✅ Online status indicators (green dot)
- ✅ "User is typing..." indicator
- ✅ Real-time message delivery via Reverb
- ✅ Message history
- ✅ Basic search in conversation list

**Estimated Development Time:** 16-24 hours

### Phase 2: Enhanced Features (1-2 weeks)
**Goal:** Feature parity with modern chat apps

- Group chats (multiple participants)
- Image/file attachments (using existing Storage disk)
- Emoji reactions to messages
- Message search within conversations
- Auto-create platoon channels (one per Team)
- Read receipts (seen by X people)
- Delete messages
- Edit messages (within 5 minutes)
- Message notifications (desktop + email)

**Estimated Development Time:** 40-60 hours

### Phase 3: ArmaBattles Integration (1 week)
**Goal:** Deep integration with existing features

- **Match chat rooms** - Temporary channels created when tournament match starts
- **Server-specific chat** - Public chat per game server
- **Player profile integration** - Click username → view profile
- **Quick actions** - `/challenge @player` to start scrim
- **Scrim coordination** - Auto-create chat when scrim is scheduled
- **GM tools** - Special channels for Game Masters
- **Referee tools** - Private channels for match officials
- Message pinning (important announcements)
- Chat moderation tools (mute, ban, delete)

**Estimated Development Time:** 30-40 hours

### Phase 4: Advanced Features (Future)
**Goal:** Compete with Discord/Steam

- Message threads/replies
- @mentions with notifications
- Rich embeds (YouTube, Twitch, Steam links)
- Voice messages
- Message formatting (bold, italic, code blocks)
- Custom emoji/stickers
- Chat themes/appearance settings
- Mobile push notifications (requires mobile app)
- Chatbots/integrations (e.g., "ArmaBattles Bot" for stats)

**Estimated Development Time:** 60-80 hours

### Phase 5: Voice Chat (2-4 weeks)
**Goal:** Real-time voice communication

#### Option A: WebRTC (Native Implementation)
**Pros:**
- Full control over infrastructure
- No per-minute costs
- Peer-to-peer (low latency)

**Cons:**
- Complex to implement
- Requires STUN/TURN servers for NAT traversal
- Harder to scale for large groups

**Tech Stack:**
- WebRTC API (browser native)
- Coturn (open source TURN server)
- SimplePeer or PeerJS library

**Use Cases:** 1-on-1 calls, small team voice (<5 people)

**Estimated Cost:** $20-50/month for TURN server

#### Option B: Third-Party Service (LiveKit/Agora)
**Pros:**
- Easy SDK integration
- Handles scaling automatically
- Great audio quality
- Recording/playback features

**Cons:**
- Per-minute pricing
- Vendor lock-in

**Recommended:** LiveKit (open source, self-hostable)

**Pricing:** ~$0.01-0.05 per participant-minute

**Use Cases:** Large platoon voice channels, tournament broadcasting

#### Option C: Self-Hosted (Jitsi Meet)
**Pros:**
- Open source
- Free to use
- Can embed in website
- Feature-rich (screen sharing, recording)

**Cons:**
- Requires separate server
- More maintenance
- Can be resource-intensive

**Tech Stack:**
- Jitsi Meet (self-hosted)
- Nginx proxy
- Dedicated server/VM

**Estimated Cost:** $20-100/month depending on usage

**Recommended Approach:**
Start with **LiveKit** for quick implementation, migrate to self-hosted WebRTC later if costs become too high.

**Implementation:**
```html
<!-- Voice call button in chat header -->
<button @click="startVoiceCall()" class="p-2 hover:bg-white/5 rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
    </svg>
</button>

<!-- Voice call overlay -->
<div x-show="inVoiceCall" class="fixed bottom-20 right-4 bg-gray-900 border border-white/10 rounded-2xl p-4 shadow-2xl">
    <div class="flex items-center gap-3 mb-4">
        <img :src="callParticipant.avatar" class="w-12 h-12 rounded-full">
        <div>
            <p class="font-semibold text-white" x-text="callParticipant.name"></p>
            <p class="text-xs text-gray-400" x-text="callDuration"></p>
        </div>
    </div>
    <div class="flex gap-2">
        <button @click="toggleMute()" :class="isMuted ? 'bg-red-600' : 'bg-white/10'" class="flex-1 p-2 rounded-lg">
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            </svg>
        </button>
        <button @click="endCall()" class="flex-1 p-2 bg-red-600 rounded-lg">
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
            </svg>
        </button>
    </div>
</div>
```

## Integration Points with Existing ArmaBattles Features

### 1. Platoon (Team) System
**Auto-create team chat when platoon is created:**
```php
// app/Models/Team.php
protected static function booted() {
    static::created(function ($team) {
        $conversation = Conversation::create([
            'type' => 'team_chat',
            'name' => $team->name,
        ]);

        foreach ($team->members as $member) {
            $conversation->participants()->create([
                'user_id' => $member->user_id,
            ]);
        }

        $team->update(['conversation_id' => $conversation->id]);
    });
}
```

### 2. Tournament Matches
**Create temporary match chat:**
```php
// When match starts
TournamentMatch::updated(function ($match) {
    if ($match->status === 'in_progress' && !$match->conversation_id) {
        $conversation = Conversation::create([
            'type' => 'match_chat',
            'name' => "Match #{$match->id} Chat",
        ]);

        // Add all participants from both teams
        foreach ($match->participants as $participant) {
            $conversation->participants()->create([
                'user_id' => $participant->user_id,
            ]);
        }

        // Add referees/observers
        $conversation->participants()->createMany(
            User::where('role', 'referee')->get()->map(fn($u) => ['user_id' => $u->id])
        );

        $match->update(['conversation_id' => $conversation->id]);
    }
});
```

### 3. Player Profiles
**Add "Send Message" button to player profiles:**
```html
<!-- In resources/views/profile/public.blade.php -->
@auth
    @if(auth()->id() !== $user->id)
        <a href="{{ route('chat.start', $user->id) }}"
           class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded-xl transition">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Send Message
        </a>
    @endif
@endauth
```

### 4. Server Pages
**Server-specific public chat:**
```php
// Create public chat for each server
Server::created(function ($server) {
    Conversation::create([
        'type' => 'server_chat',
        'name' => "{$server->name} Chat",
        'server_id' => $server->id,
        'is_public' => true,
    ]);
});
```

### 5. Scrim System
**Auto-create scrim coordination chat:**
```php
// When scrim is scheduled
ScrimMatch::updated(function ($scrim) {
    if ($scrim->status === 'scheduled' && !$scrim->conversation_id) {
        $conversation = Conversation::create([
            'type' => 'scrim_chat',
            'name' => "{$scrim->homeTeam->name} vs {$scrim->awayTeam->name}",
        ]);

        // Add both team members
        $scrim->homeTeam->members->each(fn($m) =>
            $conversation->participants()->create(['user_id' => $m->user_id])
        );
        $scrim->awayTeam->members->each(fn($m) =>
            $conversation->participants()->create(['user_id' => $m->user_id])
        );

        $scrim->update(['conversation_id' => $conversation->id]);
    }
});
```

## Security & Moderation

### Message Filtering
```php
// app/Services/ChatModerationService.php
class ChatModerationService {
    protected $bannedWords = ['spam', 'offensive', ...];

    public function filterMessage($content) {
        // Check for banned words
        foreach ($this->bannedWords as $word) {
            if (stripos($content, $word) !== false) {
                return ['allowed' => false, 'reason' => 'Banned word detected'];
            }
        }

        // Check for spam (rapid messages)
        $recentMessages = Message::where('user_id', auth()->id())
            ->where('created_at', '>', now()->subSeconds(10))
            ->count();

        if ($recentMessages > 5) {
            return ['allowed' => false, 'reason' => 'Spam detected'];
        }

        return ['allowed' => true];
    }
}
```

### Admin Tools
- Mute user (prevent sending messages for X hours)
- Ban user from specific conversation
- Delete offensive messages
- View chat logs
- Report system (flag messages for review)

## Performance Optimization

### Message Pagination
```php
// Only load last 50 messages initially
public function getMessages($conversationId) {
    return Message::where('conversation_id', $conversationId)
        ->latest()
        ->take(50)
        ->get()
        ->reverse();
}
```

### Caching
```php
// Cache conversation list
Cache::remember("user.{$userId}.conversations", 60, function() use ($userId) {
    return Conversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
        ->with('latestMessage')
        ->get();
});
```

### Database Indexes
```sql
-- Speed up common queries
CREATE INDEX idx_messages_conversation_created ON messages(conversation_id, created_at DESC);
CREATE INDEX idx_participants_user ON conversation_participants(user_id);
CREATE INDEX idx_messages_user ON messages(user_id);
```

## Advantages of Building In-House

✅ **Perfect Integration**
- Direct links to platoons, matches, players, servers
- Unified authentication (Steam login)
- Same design language as rest of site

✅ **Data Ownership**
- All messages stored in your PostgreSQL database
- Full control over data retention policies
- Can make chat history searchable/indexable

✅ **Custom Features**
- `/challenge @player` slash commands
- Auto-create match/scrim channels
- Integration with tournament brackets
- Server status in chat
- Player stats in chat cards

✅ **No External Dependencies**
- Not reliant on Discord/Stoat/third-party service
- No risk of service shutdown
- No API rate limits

✅ **Monetization Potential**
- Premium features (voice chat, file storage limits)
- Ads-free experience for supporters

## Disadvantages to Consider

❌ **Development Time**
- Initial MVP: 2-3 days
- Full feature set: 2-4 weeks
- Voice chat: +2-4 weeks

❌ **Maintenance Burden**
- Moderation (spam, abuse, offensive content)
- Bug fixes and feature requests
- Scaling as user base grows

❌ **Infrastructure Costs**
- Database storage for messages/files
- CDN for file attachments
- TURN server for voice chat (if using WebRTC)

❌ **Mobile Experience**
- Web-only initially (works on mobile browsers)
- Native apps require React Native/Flutter (much more work)
- Push notifications require service workers + backend

❌ **User Adoption**
- Users already comfortable with Discord
- Need to convince community to switch
- "Why not just use Discord?" pushback

## Recommended Approach

**Start Small, Iterate Based on User Feedback:**

1. **Week 1-2:** Build MVP (DM + platoon chat)
2. **Soft launch** to small group of users (platoon leaders, tournament organizers)
3. **Gather feedback** - do people actually use it?
4. **Week 3-4:** Add requested features based on feedback
5. **Public launch** - announce to full community
6. **Monitor usage** - track active users, messages per day
7. **Decide on voice chat** - only if text chat sees good adoption

**Success Metrics:**
- 50+ daily active users after 1 month
- 500+ messages sent per week
- Positive feedback from platoon leaders
- Reduction in "join our Discord" links on site

If metrics are good → invest in voice chat and advanced features.
If metrics are poor → reconsider if in-house chat is necessary.

## Estimated Total Development Time

| Phase | Time Estimate |
|-------|---------------|
| **MVP (DM + Text)** | 2-3 days (16-24 hours) |
| **Enhanced Features** | 1-2 weeks (40-60 hours) |
| **ArmaBattles Integration** | 1 week (30-40 hours) |
| **Polish + Bug Fixes** | 3-5 days (20-30 hours) |
| **Voice Chat (Optional)** | 2-4 weeks (60-100 hours) |
| **TOTAL (without voice)** | **3-4 weeks** |
| **TOTAL (with voice)** | **5-8 weeks** |

## Next Steps

1. **Decision:** Get community feedback - would they use in-site chat vs Discord?
2. **Proof of Concept:** Build basic DM system (1 day sprint)
3. **User Testing:** Show to 5-10 active users, get feedback
4. **Full Implementation:** If feedback is positive, proceed with full MVP
5. **Iterate:** Add features based on actual usage patterns

---

**Last Updated:** 2026-02-11
**Status:** Planning Phase
**Owner:** ArmaBattles Development Team

---

# Content Creators System - Future Features

## Overview

The Content Creators system allows streamers and content creators to register their channels and submit highlight clips for community voting. Two additional features are planned for future implementation to enhance creator visibility and streamline verification.

## Implemented Features (2026-02-13)

✅ **Auto-Approval System** - Clips automatically approve when reaching vote threshold (`clip_approval_threshold` site setting, default 10 votes)
✅ **Duration Validation** - Video submissions enforce max duration limit (`clip_max_duration_seconds` site setting, default 120 seconds)

## Planned Features

### 1. Automatic Creator Verification Based on Follower Count

**Goal:** Streamline the creator verification process by automatically granting verified status to creators who meet follower thresholds.

**Site Setting:**
- `creator_verification_followers` (integer, default: 1000) - Minimum follower count required for auto-verification

**Implementation Requirements:**

1. **API Integration** - Fetch real-time follower counts from each platform:
   - **Twitch:** Use Twitch API v5/Helix endpoint `GET /users` → `followers` field (requires OAuth app credentials)
   - **YouTube:** Use YouTube Data API v3 endpoint `GET /channels` → `statistics.subscriberCount` (requires API key)
   - **TikTok:** Use TikTok API `GET /user/info` → `follower_count` (requires Business account)
   - **Kick:** No official API - may require web scraping or manual verification

2. **Service Layer:**
   ```php
   // app/Services/CreatorVerificationService.php
   class CreatorVerificationService {
       public function checkVerificationEligibility(ContentCreator $creator): bool
       {
           $threshold = (int) site_setting('creator_verification_followers', 1000);
           $followerCount = $this->fetchFollowerCount($creator);

           if ($followerCount >= $threshold) {
               $creator->update(['is_verified' => true, 'verified_at' => now()]);
               return true;
           }

           return false;
       }

       private function fetchFollowerCount(ContentCreator $creator): int
       {
           return match($creator->platform) {
               'twitch' => $this->getTwitchFollowers($creator->channel_url),
               'youtube' => $this->getYouTubeSubscribers($creator->channel_url),
               'tiktok' => $this->getTikTokFollowers($creator->channel_url),
               'kick' => 0, // Manual verification required
           };
       }
   }
   ```

3. **Scheduled Task:**
   - Add command `creators:check-verification` to run daily
   - Re-verify existing creators periodically (e.g., weekly)
   - Send notification when creator is auto-verified

4. **Environment Variables:**
   ```
   TWITCH_CLIENT_ID=
   TWITCH_CLIENT_SECRET=
   YOUTUBE_API_KEY=
   TIKTOK_ACCESS_TOKEN=
   ```

**Estimated Development Time:** 8-12 hours (API integration + testing)

**Challenges:**
- Rate limits on platform APIs (especially YouTube: 10,000 quota/day)
- TikTok API access restricted to Business accounts
- Kick has no official API (web scraping unreliable)
- Follower counts may fluctuate, causing verification revocation issues

---

### 2. Featured Creator Slots on Homepage

**Goal:** Showcase top verified creators prominently on the homepage to drive traffic to their channels and encourage new creator registrations.

**Site Setting:**
- `creator_featured_slots` (integer, default: 6) - Number of creators to feature on homepage

**Implementation Requirements:**

1. **Featured Creator Selection Logic:**
   - Option A: Manual admin selection via flag (`is_homepage_featured` boolean)
   - Option B: Automatic rotation based on criteria (most followers, most recent clips, most active)
   - Option C: Hybrid - admin can manually feature, otherwise auto-select from verified creators

2. **Homepage Controller Update:**
   ```php
   // app/Http/Controllers/HomeController.php
   public function index()
   {
       $featuredCreators = ContentCreator::query()
           ->where('is_verified', true)
           ->where('is_homepage_featured', true) // Manual selection
           ->orWhere(function($query) {
               // Auto-fill remaining slots if manual < creator_featured_slots
               $query->where('is_verified', true)
                     ->orderByDesc('follower_count') // Assumes follower_count column added
                     ->limit(site_setting('creator_featured_slots', 6));
           })
           ->with('user')
           ->get();

       return view('home', compact('featuredCreators'));
   }
   ```

3. **Database Migration:**
   ```php
   Schema::table('content_creators', function (Blueprint $table) {
       $table->boolean('is_homepage_featured')->default(false)->after('is_verified');
       $table->integer('follower_count')->nullable()->after('channel_name'); // Cached from API
       $table->index('follower_count');
   });
   ```

4. **Admin Panel:**
   - Add "Feature on Homepage" toggle to `/admin/creators/{id}/edit`
   - Show preview of current featured creators
   - Drag-and-drop ordering for manual selection

5. **Homepage View:**
   ```html
   <!-- resources/views/home.blade.php -->
   <section class="mb-16">
       <h2 class="text-3xl font-bold mb-6">Featured Content Creators</h2>
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
           @foreach($featuredCreators as $creator)
               <a href="/creators/{{ $creator->id }}" class="glass-card p-6 hover:border-green-500/30 transition">
                   <div class="flex items-center gap-4 mb-4">
                       <img src="{{ $creator->user->avatar }}" class="w-16 h-16 rounded-full">
                       <div>
                           <h3 class="font-semibold text-white flex items-center gap-2">
                               {{ $creator->channel_name }}
                               <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                   <path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                               </svg>
                           </h3>
                           <p class="text-sm text-gray-400">{{ $creator->platform_name }}</p>
                       </div>
                   </div>
                   <p class="text-sm text-gray-300 mb-4">{{ Str::limit($creator->bio, 100) }}</p>
                   <div class="flex items-center justify-between text-sm">
                       <span class="text-gray-400">{{ number_format($creator->follower_count) }} followers</span>
                       <span class="text-green-400">View Channel →</span>
                   </div>
               </a>
           @endforeach
       </div>
       <div class="text-center mt-8">
           <a href="/creators" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-500 rounded-xl transition">
               View All Creators
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
               </svg>
           </a>
       </div>
   </section>
   ```

**Estimated Development Time:** 4-6 hours (UI + admin controls)

**Benefits:**
- Increased visibility for top creators
- Encourages new creator registrations
- Drives traffic to creator channels
- Professional appearance for homepage

---

## Priority & Timeline

**Phase 1 (Completed):** ✅ Auto-approval and duration validation (2026-02-13)

**Phase 2 (Q1 2026):** Featured Creator Slots
- Lower complexity, immediate visual impact
- No external API dependencies
- Can be implemented independently

**Phase 3 (Q2 2026):** Automatic Verification
- Requires API credentials and quota management
- Depends on platform API availability
- Higher maintenance burden (API changes, rate limits)

**Recommendation:** Implement Featured Slots first for quick wins, then tackle Auto-Verification once API infrastructure is established.

---

**Last Updated:** 2026-02-13
**Status:** Features 1-2 Implemented, Features 3-4 Planned
