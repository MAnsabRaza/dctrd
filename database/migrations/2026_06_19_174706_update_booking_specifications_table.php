use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_specifications', function (Blueprint $table) {
            // values column remove
            $table->dropColumn('values');

            // icon column add
            $table->string('icon')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('booking_specifications', function (Blueprint $table) {
            // values wapas add (rollback ke liye)
            $table->json('values')->nullable();

            // icon remove
            $table->dropColumn('icon');
        });
    }
};